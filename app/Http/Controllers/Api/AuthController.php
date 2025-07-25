<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Auth;

class AuthController extends Controller
{
    public function __construct(
        public UserService $userService
    ) {}

    /**
     * Register a newly created user resource in storage.
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $userDTO = $request->toDto();
            return $this->userService->createUser($userDTO);
        } catch (Exception $e) {
            return response()->json([
                "status" => false,
                "message" => "User registration failed: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login a user and return a token.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only("email", "password");
        if (Auth::attempt($credentials)) {
            return response()->json(
                [
                    "status" => true,
                    "message" => "User logged in successfully",
                    "token" => $request->user() ? $request->user()->createToken("api")->plainTextToken : null,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Invalid credentials",
                ],
                401
            );
        }
    }
    /**
     * Logout the specified user.
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            // Logging out the current user session
            $user->currentAccessToken()->delete();
            return response()->json(
                [
                    "status" => true,
                    "message" => $user->name . " was logged out from active session.",
                ],
                200
            );
        }

        return response()->json(
            [
                "status" => false,
                "message" => "User could not be authenticated.",
            ],
            401
        );
    }

    /**
     * Logout from all active sessions
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function logoutAllDevices(Request $request): JsonResponse
    {
        $user = $request->user();
        try {
            if ($user) {
                // Logging out from all active sessions
                $user->tokens()->delete();

                return response()->json(
                    [
                        "status" => true,
                        "message" => $user->name . " was logged out from all active sessions.",
                    ],
                    200
                );
            }
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }

        return response()->json(
            [
                "status" => false,
                "message" => "No active session found for this user.",
            ],
            401
        );
    }

    /**
     * Handle forgot password request.
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $email = $request->input('email');
        if (!$email) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Email is required.",
                ],
                400
            );
        }

        // Here you would typically send a password reset link to the user's email.
        // For simplicity, we will just return a success message.
        return response()->json(
            [
                "status" => true,
                "message" => "Password reset link sent to " . $email,
            ],
            200
        );
    }
}
