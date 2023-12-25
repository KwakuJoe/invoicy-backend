<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Jobs\PasswordResetSuccessJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ForgetPasswordRequest;
use App\Jobs\PasswordResetJob;
use App\Models\PasswordResetTokens;
use App\Notifications\EmailVerificationNotifier;
use App\Notifications\PasswordResetNotifier;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Crypt;




class PasswordResetController extends Controller
{


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
            $random = $token = Str::random(10);
            $token = Crypt::encryptString($random);
            $dateTime = Carbon::now();

             //createing signed url
            $frontendUrl = env('FRONTEND_URL');
            $expires = now()->addMinutes(30); // The link will expire in 30 minutes
            // $signature = hash_hmac('sha256', $user->email, 'invoice@secret.com'); // Replace 'your-secret-key' with your actual secret key
            $fullSignature = $token . '/'. $expires->timestamp . '/' . $user->email;
            $signedUrl = $frontendUrl . '/auth/password/edit/' . $fullSignature;


            // $url =  URL::temporarySignedRoute(
            //     'resetPassword', now()->addMinutes(30),
            //     [
            //         'token' => $token,
            //         'email' => $user->email
            //     ]
            // );

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
             PasswordResetJob::dispatch($user, $signedUrl);
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

    public function resetPassword($token, $expiry, $email){

        // if (!$request->hasValidSignature()) {

        //     return response()->json([
        //         'status'=> 'failed',
        //         'message'=> 'Password reset link is invalid or expired',
        //         'token' => null
        //     ], 403);

        // }


        // return response()->json([
        //     'status'=> 'success',
        //     'message'=> 'Password reset link is invalid or expired',
        //     'data' => [
        //         'token' => $token,
        //         'email' => $email
        //     ]
        // ], 200);


            // check whether password reset token is vaid
            $passwordToken = QueryBuilder::for(PasswordResetTokens::class)->where('token', $token)
            ->first();

            if(!$passwordToken){
                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'Url token is invalid',
                    'data' => null
                ], 403);
            }

            // $frontendUrl = env('FRONTEND_URL');
            // $externalUrl = $frontendUrl;
            // // Recreate the expected signature
            // $expectedSignature = hash_hmac('sha256', $email, 'invoice@secret.com');  // Replace 'your-secret-key' with your actual secret key

            // Compare the signatures
            // if ($expectedSignature  !== $signature) {
            //      // Return a 403 Forbidden response for an invalid signature
            //     return response()->json([
            //         'status'=> 'failed',
            //         'message'=> 'Invalid signature',
            //         'data' => null
            //     ], 403);
            // }

            // Optionally, check for expiry if you've appended an expiration timestamp
            // Check if the URL is expired
                $currentTime = now();
                $expiresParam = Carbon::createFromTimestamp($expiry);

            if ($currentTime->greaterThan($expiresParam)) {
                // Return a 403 Forbidden response if the URL has expired
                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'URL has expired',
                    'data' => null
                ], 403);
            }

            // If all checks pass, the URL is valid
            return response()->json([
                'status'=> 'success',
                'message'=> 'URL is valid',
                'data' => [
                    'token' => $token,
                        'exoect-signaure' =>$expiry,
                    'expiry' => $expiry,
                    'email' => $email
                ]
            ], 200);

    }


    public function updatePassword(UpdatePasswordRequest $request){


        try  {

            $validatedData = $request->validated();

            $user = User::where('email', $validatedData['email'])->first();

            if(!$user){

                // return redirect()->back()->with('failed', 'User with this email, not found :(?');

                return response()->json([
                    'status'=> 'failed',
                    'message'=> 'User with email does not exist',
                    'data' => null
                ], 404);

            }

            $user->password = Hash::make($validatedData['password']);
            $user->save();

            PasswordResetSuccessJob::dispatch($user);

            // return redirect()->back()->with('status', 'Your password has been reset. You can now log in with your new password.');
            return response()->json([
                'status'=> 'success',
                'message'=> 'Password successfully updated',
                'data' => $user
            ], 200);

        } catch(\Exception $e){

            // return redirect()->back()->with('failed', 'Error, trying to update password, try again?');
            return response()->json([
                'status'=> 'failed',
                'message'=> $e->getMessage(),
                'data' => null
            ], 500);
        }

    }
}
