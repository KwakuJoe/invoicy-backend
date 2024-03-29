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
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Storage;



class AuthenticationController extends Controller
{
    // User Login
    public function login(LoginRequest $request)
    {

        try {

            $credentials = $request->validated();

            // check creditial is false
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    "status" => "failed",
                    "message" => "Invalid credential, please try again",
                    "data" => null
                ], 401);
            }
            //else find user and generate tokens give am
            $user = User::where("email", $credentials["email"])->first();

            // create token for user
            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Authenticates Succesfully :)',
                'token' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'token' => [
                    'token' => null,
                    'token_type' => null,
                ]
            ], 500);
        }
    }


    public function register(RegisterRequest $request)
    {

        try {

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
            // generate expiry url link with token and users/email
            $random = $token = Str::random(10);
            $token = Crypt::encryptString($random);
            // $dateTime = Carbon::now();

            //createing signed url
            $frontendUrl = env('FRONTEND_URL');
            $expires = now()->addMinutes(30); // The link will expire in 30 minutes
            // $signature = hash_hmac('sha256', $user->email, 'invoice@secret.com'); // Replace 'your-secret-key' with your actual secret key
            $fullSignature = $token . '/' . $expires->timestamp . '/' . $user->email;
            $signedUrl = $frontendUrl . '/auth/verified/' . $fullSignature;

            // send notification using queues
            UserCreatedJob::dispatch($user, $signedUrl);


            return response()->json([
                'status' => 'success',
                'message' => 'Account created Succesfully :)',
                'token' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'token' => [
                    'token' => null,
                    'token_type' => null,
                ]
            ], 500);
        }
    }


    // public function sendEmailVerifcation1($email)
    // {

    //     try {

    //         // find user with the passed email
    //         $user = User::where('email', $email)->first();

    //         if (!$user) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'User is not authenticated or not found',
    //                 'user' => null
    //             ], 404);
    //         }

    //         if ($user->email_verified_at !== null) {
    //             // do something
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'User account is already verified',
    //                 'user' => null
    //             ], 403);
    //         }

    //         // send email first and asign token to user
    //         $email_token = Crypt::encryptString($user->email);

    //         // update this fields
    //         $user->email_verification_token = $email_token;
    //         $user->save();

    //         // generate a signe url

    //         // generate a signe url
    //         $url =  URL::temporarySignedRoute(
    //             'emailVerifiedView',
    //             now()->addMinutes(30),
    //             ['token' => $email_token],
    //         );

    //         // send notification using queues
    //         UserCreatedJob::dispatch($user, $url);
    //         // $user->notify(new EmailVerificationNotifier($user, $url));


    //         //generate toke for
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Email as been sent to this address ' . $email . ' Visit to  confirm',
    //             'url' => $url,
    //             'user' => $user,
    //             'token' => $email_token,
    //         ], 200);
    //     } catch (\Exception $exception) {

    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => $exception->getMessage(),
    //             'url' => null,
    //             'token' => null,
    //         ], 200);
    //     }
    // }

    public function sendEmailVerifcation($email)
    {

        try {


            $user = User::where("email", $email)->first();

            if (!$user) {

                return response()->json([
                    'status' => 'failed',
                    'message' => 'This email does not associate with any account',
                    'data' => null
                ], 404);
            }


            if ($user->email_verified_at !== null) {
                // do something
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User account is already verified',
                    'user' => null
                ], 403);
            }


            // generate expiry url link with token and users/email
            $random = $token = Str::random(10);
            $token = Crypt::encryptString($random);
            // $dateTime = Carbon::now();

            //createing signed url
            $frontendUrl = env('FRONTEND_URL');
            $expires = now()->addMinutes(30); // The link will expire in 30 minutes
            // $signature = hash_hmac('sha256', $user->email, 'invoice@secret.com'); // Replace 'your-secret-key' with your actual secret key
            $fullSignature = $token . '/' . $expires->timestamp . '/' . $user->email;
            $signedUrl = $frontendUrl . '/auth/verified/' . $fullSignature;


            // update this fields
            $user->email_verification_token = $token;
            $user->save();

            // send notification using queues
            UserCreatedJob::dispatch($user, $signedUrl);

            return response()->json([
                'status' => 'success',
                'message' => 'Email verification link sent to your email',
                'data' => [
                    'email' => $email,
                    'token' => $token,
                    'expiry' => $expires,
                    'user' => $user
                ],
            ], 200);


            // $user->password = bcrypt($validated['password']);


        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }


    // public function verifyEmail1(Request $request, $token)
    // {

    //     if (!$request->hasValidSignature()) {
    //         // abort(401);
    //         return view('emails.verify.email-verified-error');
    //     }

    //     // decrypt token
    //     $decrypted = Crypt::decryptString($token);

    //     // return view('verify.email-verified');
    //     $user = User::where('email', $decrypted)->first();

    //     // if no user found direct 404;
    //     if (!$user) {
    //         return view('404.404');
    //     }

    //     // empty rember me token
    //     $user->email_verification_token = null;

    //     // filed verified at with current time
    //     $user->email_verified_at = Carbon::now();

    //     $user->save();

    //     // show this page
    //     return view('emails.verify.email-verified');
    // }


    public function verifyEmail($token, $expiry, $email)
    {

        try {
            // check whether password reset token is vaid
            $user = QueryBuilder::for(User::class)->where('email', $email)
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User with this email does not exist',
                    'data' => null
                ], 403);
            }

            // check for the expiry time
            $currentTime = now();
            $expiresParam = Carbon::createFromTimestamp($expiry);

            if ($currentTime->greaterThan($expiresParam)) {
                // Return a 403 Forbidden response if the URL has expired
                return response()->json([
                    'status' => 'failed',
                    'message' => 'URL has expired',
                    'data' => null
                ], 403);
            }


            // empty rember me token
            $user->email_verification_token = null;

            // filed verified at with current time
            $user->email_verified_at = Carbon::now();

            $user->save();

            // If all checks pass, the URL is valid
            return response()->json([
                'status' => 'success',
                'message' => 'URL is valid',
                'data' => [
                    'token' => $token,
                    'exoect-signaure' => $expiry,
                    'expiry' => $expiry,
                    'email' => $email
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }


    public function updateProfile(Request $request)
    {

        try {

            $request->validate([
                'id' => 'required | numeric | exists:users,id',
                'organisation' => 'sometimes|string',
                'email' => 'sometimes|email',
                'phone' => 'sometimes|numeric',
                'bio' => 'sometimes|string',
                'address' => 'sometimes|string',
                'avatar' => 'sometimes|image|mimes:jpeg,png,gif|max:5120',
            ]);

            $user = User::where("id", $request->id)->first();

            if (!$user) {

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to find user with this account',
                    'data' => null
                ], 404);
            }

            if ($request->hasFile('avatar')) {

                $name = str()->uuid() . '.' . $request->avatar->getClientOriginalExtension();
                $destination_path = 'images/avatars';

                // check if user have avatar and delete it
                if ($user->avatar) {
                    $old_user_avatar = $user->avatar;
                    Storage::delete($old_user_avatar);
                }
                //get path and store new avatar image
                $path = $request->avatar->storeAs($destination_path, $name);

                // store
                $user->avatar = $path;
            }

            $user->organisation = $request->organisation;
            $user->email = $request->email;
            $user->phone  = $request->phone;
            $user->bio = $request->bio;
            $user->address = $request->address;

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User profile updated successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
