<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PreferenceResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     * This method retrieves user preferences.
     */
    public function get()
    {
        $preferences = auth('sanctum')->user()->preference;
        if (!$preferences) {
            return response()->json([
                'status' => false,
                'message' => 'No preferences found'
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => new PreferenceResource($preferences)
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     * @return \Illuminate\Http\JsonResponse
     * This method updates or creates user preferences.
     * @OA\Post(
     *     path="/api/preferences",
     *     summary="Save user preferences",
     *     tags={"Preferences"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="category", type="string", example="technology"),
     *             @OA\Property(property="source", type="string", example="News API")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference stored",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="category", type="string"),
     *                 @OA\Property(property="source", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'category' => 'nullable|string',
                'source' => 'nullable|string',
            ]);

            $user = auth('sanctum')->user();
            $preference = $user->preferences()->updateOrCreate([], $request->only('article_id', 'user_id'));
            if (!$preference) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to store preference'
                ], 422);
            }
            return response()->json([
                'status' => true,
                'data' => $preference
            ], 201);
        } catch (Exception $exception) {
            Log::error(
                'Failed to store preference: ' . $exception->getMessage(),
                ['error' => $exception]
            );
            return response()->json([
                'status' => false,
                'message' => 'Failed to store preference: ' . $exception->getMessage()
            ], 422);
        }
    }
}
