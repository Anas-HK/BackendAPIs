<?php

namespace App\Http\Controllers;

use App\Mail\OtpVerification;
use App\Models\Otp;
use App\Models\User;
use App\Models\UserTemp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
            // print_r($user->business_id); Business_id is being sent successfully
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

        // Check if the OTP code has expired
        $expirationTime = Carbon::now()->subMinutes(5);
        if ($otp->created_at->lt($expirationTime)) {
            // Mark OTP as expired
            $otp->status = 0; // 0 means expired
            $otp->save();
            return response()->json(['status' => 'failure', 'message' => 'OTP code has expired. Please generate a new one.']);
        }

        // Retrieve the email associated with the OTP
        $email = $otp->email;


        // Retrieve the user data from the UserTemp table
        $userTemp = UserTemp::where('email', $email)->first();

        // Store the password before deleting the UserTemp object
         $rawPassword = $userTemp->password;

        // Mark OTP as used
        $otp->is_used = 1;
        $otp->save();

        // Create an instance of ProfileSetup
        $profileSetup = new BusinessProfileController();
        $businessId = $profileSetup->BusinessId();

        // Proceed with the registration process
        try {
            $user = new User;
            $user->name = $userTemp->name;
            $user->email = $userTemp->email;
            $user->password = app('hash')->make($rawPassword);
            $user->phone = $userTemp->phone;
            $user->date_of_birth = $userTemp->date_of_birth;
            $user->status = $userTemp->status;
            $user->user_type_id = $userTemp->user_type_id;
            $user->category_id = $userTemp->category_id;
            $user->business_id = $businessId;
            $user->is_deleted = $userTemp->is_deleted;
            $user->consent = $userTemp->consent;
            // If otp is verified than verified = 1
            $user->verified = 1;
            $user->UUID = $userTemp->UUID;

            // Delete the user data from the UserTemp table
            $userTemp->delete();

            if ($user->save()) {
                // Add user data to the request
                $request->merge(['user' => $user, 'email' => $user->email, 'password' => $rawPassword]);

                // Proceed with the login process or return a success response
                // I don't need to only send business_id as Maaz can access the business_id from the whole user table's object which I'm sending in the request.
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => 'Error registering user: ' . $e->getMessage()]);
        }
    }

    public function registerConsumer(Request $request) {
        // Validate the incoming request data
        return $this->validateTheIncomingRequestData($request);
    }

    public function registerBusiness(Request $request) {
        // Validate the incoming request data
        return $this->validateTheIncomingRequestData($request);
    }

    public function validateTheIncomingRequestData(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Maximum length of 255 characters
            'email' => 'required|email|max:100|unique:users', // Maximum length of 100 characters
            'password' => 'required|string|max:255|min:6', // Maximum length of 255 characters
            'phone' => 'required|string|max:100|unique:users', // Maximum length of 100 characters
            'date_of_birth' => 'required|date',
            'status' => 'required|integer',
            'user_type_id' => 'required|integer',
            'category_id' => 'required|integer',
            // 'business_id' => 'required|integer',
            'is_deleted' => 'required|integer',
            'consent' => 'required|integer',
            // 'verified' => 'required|integer',
            'UUID' => 'required|integer',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        $otp = new Otp;
        $otp->email = $request->email;
        $otp->code = mt_rand(1000, 9999); // Generate random OTP code
        $otp->status = 1; // Active
        $otp->save();

        $otpCode = $otp->code;

        // Saving all data of user temporarily to access in verifyOTP function
        $userTemp = new UserTemp;
        $userTemp->name = $request->name;
        $userTemp->email = $request->email;
        $userTemp->password = $request->password;
        $userTemp->phone = $request->phone;
        $userTemp->date_of_birth = $request->date_of_birth;
        $userTemp->status = $request->status;
        $userTemp->user_type_id = $request->user_type_id;
        $userTemp->category_id = $request->category_id;
        // Giving business_id as null because we don't need to give any value and the real business id will be assigned in otp_verfication. Giving null won't work.
        $userTemp->business_id = 0;
        $userTemp->is_deleted = $request->is_deleted;
        $userTemp->consent = $request->consent;
        // We're not taking this because by default, verified = 0 (not verified). And we don't want it to be nullS
        // $userTemp->verified = $request->verified;
        $userTemp->UUID = $request->UUID;

        $userTemp->save();

        try {
            Mail::to($request->email)->send(new \App\Mail\OtpVerification($otpCode));
            // removing data field from response json
            return response()->json(['status' => 'success', 'message' => 'OTP has been sent to your email']);
//            return response()->json(['businessId' => $businessId]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => $e->getMessage()]);
        }
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










