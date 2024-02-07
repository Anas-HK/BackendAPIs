<?php

namespace App\Http\Controllers;

use App\Mail\OtpVerification;
use App\Models\Otp;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request) {
        // Validate the incoming request data
        // Through this if any errors occur it will be handled gently with a json response
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        $email = $request->email;
        $password = $request->password;

        // Retrieve the user record from the database based on the provided email
        $user = User::where('email', $email)->first();

        // Check if user with the provided email exists
        if (!$user) {
            return response()->json(['status' => 'failure', 'message' => 'User not found']);
        }

        // Verify the password against the stored password hash
        if (!app('hash')->check($password, $user->password)) {
            return response()->json(['status' => 'failure', 'message' => 'Invalid credentials']);
        }

        $client = new Client();
        try {
            $response = $client->post(config('service.passport.login_endpoint'), [
                "form_params" => [
                    "client_secret" => config('service.passport.client_secret'),
                    "grant_type" => "password",
                    "client_id" => config('service.passport.client_id'),
                    "username" => $request->email,
                    "password" => $request->password
                ]
            ]);

            // Before returning response, I need to save access token to users table.

            // Return the response from the OAuth token endpoint
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            // Handle request exceptions (e.g., connection errors)
            return response()->json(['status'=> 'failure', 'message' => 'your error' .  $e->getMessage()]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['status'=> 'failure', 'message' => $e->getMessage()]);
        }
    }

    // New method to handle OTP verification
    public function verifyOtp(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'otp_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        $otpCode = $request->otp_code;

        // Retrieve the OTP record from the database based on the provided OTP code
        $otp = Otp::where('code', $otpCode)
            ->where('status', 1) // Assuming 1 means active
            ->where('is_used', 0) // Assuming 0 means not used
            ->first();

        if (!$otp) {
            return response()->json(['status' => 'failure', 'message' => 'Invalid OTP']);
        }

        // Retrieve the email associated with the OTP
        $email = $otp->email;

        // Mark OTP as used
        $otp->is_used = 1;
        $otp->save();

        // Proceed with the registration process
        try {
            $user = new User;
            $user->name = $request->name;
            $user->email = $email; // Use the retrieved email
            $user->password = app('hash')->make($request->password);
            $user->phone = $request->phone;
            $user->date_of_birth = $request->date_of_birth;
            $user->status = $request->status;
            $user->user_type_id = $request->user_type_id;
            $user->category_id = $request->category_id;
            $user->is_deleted = $request->is_deleted;
            $user->consent = $request->consent;

            if ($user->save()) {
                // Proceed with the login process or return a success response
//                return response()->json(['status' => 'success', 'message' => 'User registered successfully']);
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => 'Error registering user: ' . $e->getMessage()]);
        }
    }

    public function register(Request $request) {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Maximum length of 255 characters
            'email' => 'required|email|max:100|unique:users', // Maximum length of 100 characters
            'password' => 'required|string|max:255|min:6', // Maximum length of 255 characters
            'phone' => 'required|string|max:100|unique:users', // Maximum length of 100 characters
            'date_of_birth' => 'required|date',
            'status' => 'required|integer',
            'user_type_id' => 'required|integer',
            'category_id' => 'required|integer',
            'is_deleted' => 'required|integer',
            'consent' => 'required|integer',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        $otp = new Otp;
        $otp->email = $request->email;
        $otp->code = mt_rand(100000, 999999); // Generate random OTP code
        $otp->status = 1; // Active
        $otp->save();

        $otpCode = $otp->code;

        // Pass $otpCode to the view when sending the email
        Mail::to($request->email)->send(new OtpVerification($otpCode));
        return response()->json(['status' => 'success', 'message' => 'OTP has been sent to your email']);
        // You need to implement email sending functionality here
        // Send OTP to user's email
        // Mail::to($request->email)->send(new OtpVerification($otp->code));

        // After sending email, verifying the otp
//        $otpVerificationResponse = $this->verifyOtp($request);
//
//        // Check OTP verification response
//        if ($otpVerificationResponse['status'] !== 'success') {
//            return response()->json($otpVerificationResponse);
//        }

        // Create new user
//        try {
//            $user = new User;
//            $user->name = $request->name;
//            $user->email = $request->email;
//            $user->password = app('hash')->make($request->password);
//            $user->phone = $request->phone;
//            $user->date_of_birth = $request->date_of_birth;
//            $user->status = $request->status;
//            $user->user_type_id = $request->user_type_id;
//            $user->category_id = $request->category_id;
//            $user->is_deleted = $request->is_deleted;
//            $user->consent = $request->consent;
//
//            if($user->save()) {
//                // Will call login method after successful registration, so that we can get access token right after registration
//                // The $request variable has all the registered data.
//                return $this->login($request);
//            }
//        } catch(\Exception $e) {
//            return response()->json(['status' => 'failure', 'message' => 'SQL error' .  $e->getMessage()]);
//        }
    }

    // Will delete all associated tokens with the user
    public function logout(Request $request) {
        try {
            auth()->user()->tokens()->each(function($token){
                $token->delete();
            });

            return response()->json(['status' => 'success', 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => $e->getMessage()]);
        }
    }
}










