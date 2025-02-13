<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
        $user = JWTAuth::user();
        $data = [
            "statusCode" => 200,
            "data" => [
                "id" => $user->id,
                "email" => $user->email,
                "username" => $user->username,
                "token" => $token
            ]
        ];

        return response()->json($data);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not invalidate token'], 500);
        }
    }

    public function test(Request $request)
    {
        $user = Auth::user();
        $roles = $user->getRoleNames(); // Retrieve the roles assigned to the user

        return response()->json([
            'message' => 'Authorization successful.',
            'user' => $user,
            'roles' => $roles,
        ]);
    }
}
