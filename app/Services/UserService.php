<?php
namespace App\Services;

use App\DTO\UserDTO;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(UserDTO $userDTO): JsonResponse
    {
        try {
            $user = new User();
            $user->name = $userDTO->name;
            $user->email = $userDTO->email;
            $user->password = $userDTO->password;
            $user->save();

            return response()->json([
                "status" => true,
                "message" => "User registered successfully",
                "token" => $user->createToken("api")->plainTextToken
            ], 200);
        } catch (Exception $e) {
            logger($e);
            return response()->json([
                "status" => false,
                "message" => "User registration failed: " . $e->getMessage(),
            ], 500);
        }
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updateUser(User $user, UserDTO $userDTO): User
    {
        $user->name = $userDTO->name;
        $user->email = $userDTO->email;
        if ($userDTO->password) {
            $user->password = bcrypt($userDTO->password);
        }
        $user->save();

        return $user;
    }
}
