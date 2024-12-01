<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SendPasswordResetEmailRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
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
            return response()->json(['message' => 'User registered successfully', 'user' => $user]);
        } catch (Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    /**
     * @throws ValidationException
     */
    public function login(LoginRequest $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate a Sanctum token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function sendPasswordResetEmail(SendPasswordResetEmailRequest $request)
    {
        try {
            // Send password reset link
            $response = Password::sendResetLink(
                $request->only('email')
            );

            if ($response == Password::RESET_LINK_SENT) {
                return response()->json(['message' => 'Password reset link sent.']);
            }
            return response()->json(['message' => 'Failed to send reset link.', 'data' => $response], 500);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset the user's password.
     *
     * @param PasswordResetRequest $request
     * @return JsonResponse
     */
    public function passwordReset(PasswordResetRequest $request)
    {
        // Attempt to reset the password
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset.']);
        }

        return response()->json(['message' => 'Failed to reset password.'], 500);
    }
}
