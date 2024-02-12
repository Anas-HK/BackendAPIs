<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        // Find the user with the provided email address
        $user = User::where('email', $request->email)->first();

        // If user does not exist, return error response
        if (!$user) {
            return response()->json(['message' => 'No user found with that email address.'], 404);
        }

        // Generate a random token
        // Will also set token expiry when needed
        $token = Str::random(60);

        // Store the token in the password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['email' => $user->email, 'token' => Hash::make($token), 'created_at' => date('Y-m-d H:i:s')]
        );

        // Send password reset email
        $this->sendResetPasswordEmail($user->email, $token);

        // Return success response
        return response()->json(['message' => 'We have emailed your password reset token.'], 200);
    }

    public function sendResetPasswordEmail($email, $token)
    {
        // Build email content (plain text)
        // $resetLink = 'https://yourwebsite.com/reset-password?token=' . $token;
        $resetInstructions = "To reset your password, please reply to this email with your new desired password. (Currently only resetPassword API can be used)";
        $emailBody = "Hello,\n\n";
        $emailBody .= "You've requested to reset your password.\n\n";
        $emailBody .= "Your password token is: ". $token . "\n\n";
        $emailBody .= $resetInstructions;

        // Send email
        Mail::raw($emailBody, function ($message) use ($email) {
            $message->to($email)->subject('Reset Password Instructions');
        });
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        // Find the token record from the password_resets table
        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        // If token record not found, return error response
        if (!$tokenData) {
            return response()->json(['message' => 'Invalid password reset token.'], 404);
        }

        // Verify the token
        if (!Hash::check($request->token, $tokenData->token)) {
            return response()->json(['message' => 'Invalid password reset token.'], 404);
        }

        // Find the user with the provided email address
        $user = User::where('email', $request->email)->first();

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token record from the password_resets table
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        // Return success response
        return response()->json(['message' => 'Password has been reset successfully.'], 200);
    }

}
