<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Auth\LoginUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    const TOKEN_NAME_SESSION = "session|";

    /**
     * Register a new user with its proper data
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $request->validated();
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => $request['password']
            ]);
            $saved = $user->save();
            if ($saved) {
                $response = ["status" => "success", "data" => $user];
                $httpStatus = 200;
            } else {
                $response = ["status" => "error", "message" => "Could not save new user."];
                $httpStatus = 400;
            }
        } catch (Exception $e) {
            Log::info('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error registering new user."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, config('consts.jsonHeaders'));
    }

    /**
     * Will return an api token to be used across the session using Sanctum
     */
    public function login(LoginUserRequest $request)
    {
        try {
            $request->validated();
            if (Auth::attempt(['email' => $request['email'], 'password' => $request['password']])) {
                $user = User::where('email', $request->email)->first();
                $this->logout($request);
                $token = $user->createToken(self::TOKEN_NAME_SESSION.$request->device_name)->plainTextToken;
                $response = ["status" => "success", "token" => explode("|", $token)[1]];
                $httpStatus = 200;
            }
        } catch (Exception $e) {
            Log::info('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error loging user."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, config('consts.jsonHeaders'));
    }

    /**
     * Will delete session api token
     */
    public function logout(Request $request)
    {
        try {
            $deleted = $request->user()->tokens()->where('name', self::TOKEN_NAME_SESSION.$request->device_name)->delete();
            if (!$deleted) {
                $response = ["status" => "success", "message" => "Unsuccessful logout."];
                $httpStatus = 400;
            }
            $response = ["status" => "success", "message" => "Successful logout."];
            $httpStatus = 200;
        } catch (Exception $e) {
            Log::info('An exception ocurred: ' . $e);
            $response = ["status" => "error", "message" => "Error loging out user."];
            $httpStatus = 500;
        }
        return response()->json($response, $httpStatus, config('consts.jsonHeaders'));
    }
}
