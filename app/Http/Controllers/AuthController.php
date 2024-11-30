<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $name = $request->input('name');
        $password = $request->input('password');
        try {
            $user = User::create([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make($password)
            ]);
            $user->createToken('auth_token');
            return response()->json(['message' => 'User registered successfully', 'user' => $user]);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
