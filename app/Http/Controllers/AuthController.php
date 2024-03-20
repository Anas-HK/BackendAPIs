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
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(Request $request) {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => 'Error', 'status_code':JsonResponse::HTTP_BAD_REQUEST]);
        }

        $email = $request->email;
        $password = $request->password;

        // Retrieve the user record from the database based on the provided email
        $user = User::where('email', $email)->first();

        // Check if user with the provided email exists
        if (!$user) {
            return response()->json(['status' => 'failure', 'message' => 'User not found', 'status_code':JsonResponse::HTTP_BAD_REQUEST]);
        }

        // Verify the password against the stored password hash
        if (!app('hash')->check($password, $user->password)) {
            return response()->json(['status' => 'failure', 'message' => 'Invalid credentials','status_code':JsonResponse::HTTP_BAD_REQUEST]);
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

            // Decode the response body
            $responseData = json_decode($response->getBody()->getContents(), true);

            // Include business ID in the response if user has one
            $responseData['business_id'] = $user->business_id;

            // Return the response with additional data
            return response()->json($responseData);
        } catch (RequestException $e) {
            // Handle request exceptions (e.g., connection errors)
            return response()->json(['status'=> 'failure', 'message' => 'your error' .  $e->getMessage(),'status_code':JsonResponse::HTTP_BAD_REQUEST]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['status'=> 'failure', 'message' => $e->getMessage(),'status_code':JsonResponse::HTTP_BAD_REQUEST]);
        }
    }

    // Will recieve UUID as request and need to find the otp code with that UUID, if true than will set that OTP code is_used to 1 and send new otp
    public function resendOtp(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'UUID' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'failure', 'message' => $validator->errors()->first()]);
        }

        // Find the OTP record matching the provided UUID
        $otp = Otp::where('UUID', $request->UUID)->first();

        // If no matching OTP record is found, return failure response
        if (!$otp) {
            return response()->json(['status' => 'failure', 'message' => 'No OTP record found for the provided UUID.']);
        }

        // Check if the OTP has already been used
        if ($otp->is_used) {
            return response()->json(['status' => 'failure', 'message' => 'OTP has already been used.']);
        }

        // Generate a new OTP code
        $newOtpCode = mt_rand(1000, 9999);

        // Create a new OTP record in the OTP table
        $newOtp = new Otp();
        $newOtp->email = $otp->email;
        $newOtp->code = $newOtpCode;
        $newOtp->UUID = $otp->UUID; // Assuming you want to keep the same UUID
        $newOtp->status = 1; // Assuming you want to set the status to active
        $newOtp->save();

        // Mark the existing OTP record as used
        $otp->is_used = 1;
        $otp->save();

        // Resend the new OTP email to the corresponding email address
        try {
            Mail::to($newOtp->email)->send(new \App\Mail\OtpVerification($newOtpCode));
            return response()->json(['status' => 'success', 'message' => 'New OTP has been resent to the email address.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failure', 'message' => 'Failed to resend OTP.']);
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
        return $this->validateTheIncomingRequestData($request, 1);
    }

    public function registerBusiness(Request $request) {
        // Validate the incoming request data
        return $this->validateTheIncomingRequestData($request, 3);
    }

    public function validateTheIncomingRequestData(Request $request, $userType): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Maximum length of 255 characters
            'email' => 'required|email|max:100|unique:users', // Maximum length of 100 characters
            'password' => 'required|string|max:255|min:6', // Maximum length of 255 characters
            'phone' => 'required|string|max:100|unique:users', // Maximum length of 100 characters
            // 'date_of_birth' => 'required|date',
            // 'status' => 'required|integer',
            //'user_type_id' => 'required|integer',
            'category_id' => 'required|integer',
            // 'business_id' => 'required|integer',
            // 'is_deleted' => 'required|integer',
            // 'consent' => 'required|integer',
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
        $otp->UUID = $request->UUID;
        $otp->save();

        $otpCode = $otp->code;

        // Saving all data of user temporarily to access in verifyOTP function
        $userTemp = new UserTemp;
        $userTemp->name = $request->name;
        $userTemp->email = $request->email;
        $userTemp->password = $request->password;
        $userTemp->phone = $request->phone;
        $userTemp->date_of_birth = '1900-01-01';
        $userTemp->status = 1;
        // If userType is 3 than it'll be set to business, otherwise it'll be 1 for consumer.
        if($userType == 3) {
            $userTemp->user_type_id = 3;
        }
        else {
            $userTemp->user_type_id = 1;
        }

        $userTemp->category_id = $request->category_id;
        // Giving business_id as null because we don't need to give any value and the real business id will be assigned in otp_verfication. Giving null won't work.
        $userTemp->business_id = 0;
        $userTemp->is_deleted = 0;
        $userTemp->consent = 0;
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










