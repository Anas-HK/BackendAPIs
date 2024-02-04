<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) {
        $email = $request->email;
        $password = $request->password;

        // Check if fields are not empty
        if(empty($email) || empty($password)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all fields']);
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

            // Return the response from the OAuth token endpoint
            return $response->getBody()->getContents();
        } catch (RequestException $e) {
            // Handle request exceptions (e.g., connection errors)
            return response()->json(['status'=> 'error', 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['status'=> 'error', 'message' => $e->getMessage()]);
        }
    }

    public function register(Request $request) {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        // Check if fields are not empty
        if(empty($email) || empty($password) || empty($name)) {
            return response()->json(['status' => 'error', 'message' => 'You must fill all fields']);
        }

        // Check if email is valid
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['status' => 'error', 'message' => 'You must enter a valid email']);
        }

        // Check if password is greater than 5 characters
        if(strlen($password) < 5) {
            return response()->json(['status' => 'error', 'message' => 'Password should be greater than 5 characters']);
        }

        // Check if user already exists
        if(User::where('email', '=', $email)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'User already exists']);
        }

        // Create new user
        try {
            $user = new User;
            $user->name = $name;
            $user->email = $email;
            $user->password = app('hash')->make($password);

            if($user->save()) {
                // Will call login method after successful registration, so that we can get access token right after registration
                // The $request variable has all the registered data.
                return $this->login($request);
            }
        } catch(\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
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
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}










