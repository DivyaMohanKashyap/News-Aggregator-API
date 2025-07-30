<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        public UserService $userService
    ) {
    }

    /**
     * * Register a new user.
     * @param  \App\Http\Requests\RegisterUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     operationId="registerUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-29T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User registration failed: Server error")
     *         )
     *     )
     * )
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
     * @throws \Illuminate\Auth\AuthenticationException
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Auth"},
     *     summary="Login a user",
     *     operationId="loginUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="example@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User logged in successfully"),
     *             @OA\Property(property="token", type="string", example="1|abcdefghij1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid email or password.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User login failed: Server error")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *      path="/api/v1/logout",
     *      tags={"Auth"},
     *      summary="Logout from current device",
     *      security={{"sanctum":{}}},
     *      operationId="logoutUser",
     *      @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
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
     * Logout the specified user.
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     * @throws Exception
     * @throws \Illuminate\Auth\AuthenticationException
     * @OA\Post(
     *     path="/api/v1/logout-all-devices",
     *     tags={"Auth"},
     *     summary="Logout from current device",
     *     security={{"sanctum":{}}},
     *     operationId="logoutUser",
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
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
     * @throws \Illuminate\Validation\ValidationException
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     tags={"Auth"},
     *     summary="Request password reset link",
     *     operationId="forgotPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="example@email.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User registration failed: Server error")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => true, 'message' => __($status)])
            : response()->json(['status' => false, 'message' => __($status)], 500);
    }
}
