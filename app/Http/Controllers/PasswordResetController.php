<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Jobs\PasswordResetSuccessJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class PasswordResetController extends Controller
{




    public function resetPassword($token, $email, Request $request){

        if (!$request->hasValidSignature()) {

            return view('404.404');

        }

        return view('emails.password.reset-password', [
            'token' => $token,
            'email' => $email
        ]);

    }

    public function updatePassword(UpdatePasswordRequest $request){


        try  {

            $validatedData = $request->validated();

            $user = User::where('email', $validatedData['email'])->first();

            if(!$user){

                return redirect()->back()->with('failed', 'User with this email, not found :(?');

            }

            $user->password = Hash::make($validatedData['password']);
            $user->save();

            // $name = $user -> name;

            // $delay = now()->addMinutes(1);

            // Send the notification with queue job
            // $user->notify((new PasswordResetSuccessNotification($name))->delay($delay));
            PasswordResetSuccessJob::dispatch($user);

            return redirect()->back()->with('status', 'Your password has been reset. You can now log in with your new password.');


        } catch(\Exception $e){

            return redirect()->back()->with('failed', 'Error, trying to update password, try again?');

        }

    }
}
