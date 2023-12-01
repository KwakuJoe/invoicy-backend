<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\PasswordResetJob;
use App\Jobs\UserCreatedJob;
use App\Models\PasswordResetTokens;
use App\Models\User;
use App\Notifications\EmailVerificationNotifier;
use App\Notifications\PasswordResetNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AuthenticationController extends Controller
{
    // User Login
    public function login(LoginRequest $request){

        try {

            $credentials = $request->validated();

            // check creditial is false
            if(!Auth::attempt($credentials)){
                return response()->json([
                    "status"=> "failed",
                    "message"=> "Invalid credential, please try again",
                    "data" => null
                ], 200);
            }
            //else find user and generate tokens give am
            $user = User::where("email", $credentials["email"])->first();

            // create token for user
            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'status'=> 'success',
                'message'=> 'Authenticates Succesfully :)',
                'token' => [
                    'token'=> $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);


        }catch(\Exception $e){

            return response()->json([
                'status'=> 'failed',
                'message'=> $e->getMessage(),
                'token' => [
                    'token'=> null,
                    'token_type' => null,
                ]
            ], 200);
        }
    }


    public function register(RegisterRequest $request){

        try{

            //
            $registerData = $request->validated();

            // create user
            $user = User::create($registerData);

            // create token for user
            $token = $user->createToken('api_token')->plainTextToken;

            // send email first and asign token to user
            $email_token = Crypt::encryptString($user->email);

            // update this fields
            $user->email_verification_token = $token;
            $user->save();

            // generate a signe url
            $url =  URL::temporarySignedRoute(
                'emailVerifiedView', now()->addMinutes(30),
                ['token' => $email_token],);

            // send notification using queues
            UserCreatedJob::dispatch($user, $url);


            return response()->json([
                'status'=> 'success',
                'message'=> 'Account created Succesfully :)',
                'token' => [
                    'token'=> $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);

        }catch(\Exception $e){

            return response()->json([
                'status'=> 'failed',
                'message'=> $e->getMessage(),
                'token' => [
                    'token'=> null,
                    'token_type' => null,
                ]
            ], 200);

        }

    }


    public function sendEmailVerifcation( $email){


        try{

            // find user with the passed email
            $user = User::where('email', $email)->first();

            if(!$user){
                return response()->json([
                    'status'=> 'failed',
                    'message' => 'User is not authenticated or not found',
                    'user' => null
                ], 404);
            }

            if (optional($user->email_verified_at)->isEmpty()) {
                // do something
                return response()->json([
                    'status'=> 'failed',
                    'message' => 'User account is already verified',
                    'user' => null
                ], 403);
            }

            // send email first and asign token to user
            $email_token = Crypt::encryptString($user->email);

            // update this fields
            $user->email_verification_token = $email_token;
            $user->save();

            // generate a signe url

            // generate a signe url
            $url =  URL::temporarySignedRoute(
                'emailVerifiedView', now()->addMinutes(30),
                ['token' => $email_token],);

            // send notification using queues
            UserCreatedJob::dispatch($user, $url);
            // $user->notify(new EmailVerificationNotifier($user, $url));


            //generate toke for
            return response()->json([
                'status'=> 'success',
                'message'=> 'Email as been sent to this address ' .$email. 'Visit to  confirm',
                'url' => $url,
                'user' => $user,
                'token' => $email_token,
            ], 200);

        }catch(\Exception $exception){

            return response()->json([
                'status'=> 'failed',
                'message'=> $exception->getMessage(),
                'url' => null,
                'token' => null,
            ], 200);
        }

    }

    public function verifyEmail( Request $request, $token, ){

        if (!$request->hasValidSignature()) {
            // abort(401);
            return view('emails.verify.email-verified-error');
        }

            // decrypt token
            $decrypted = Crypt::decryptString($token);

            // return view('verify.email-verified');
            $user = User::where('email', $decrypted)->first();

            // if no user found direct 404;
            if(!$user){
                return view('404.404');
            }

            // empty rember me token
            $user->email_verification_token = null;

            // filed verified at with current time
            $user->email_verified_at = Carbon::now();

            $user->save();

            // show this page
            return view('emails.verify.email-verified');
    }

    public function forgetPassword(ForgetPasswordRequest $request){

        try {

            $validated = $request->validated();
            // find user email in db

            $user = User::where("email", $validated["email"])->first();

            if(!$user){

                return response()->json([
                    'status' => 'failed',
                    'message'=> 'This email does not associate with any account',
                    'data' => null
                ], 404);

            }

            // generate expiry url link with token and users/email
            $random = str::random(10);
            $token = Crypt::encryptString($random);
            $dateTime = Carbon::now();


            $url =  URL::temporarySignedRoute(
                'resetPassword', now()->addMinutes(30),
                [
                    'token' => $token,
                    'email' => $user->email
                ]
            );

            // $message  = [
            //     'title' => 'Password Reset',
            //     'message' =>'Hello '. $user->name . ' The following url will direct to the page where you would rest your password',
            //     'url' => $url
            // ];

            $passResetToken = PasswordResetTokens::updateOrCreate(
                ['email'=> $validated['email']],
                [
                    'email' => $validated['email'],
                    'token' => $token,
                    'created_at' => $dateTime,
                ]
            );

             // send notification using queues
             PasswordResetJob::dispatch($user, $url);
            //  $user->notify(new PasswordResetNotifier($user, $url));

            return response()->json([
                'status' => 'success',
                'message' => 'Password rest link sent to your email',
                'data' => $passResetToken,
                'user' => $user,
            ], 200);


            // $user->password = bcrypt($validated['password']);


        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 200);
        }
    }


}
