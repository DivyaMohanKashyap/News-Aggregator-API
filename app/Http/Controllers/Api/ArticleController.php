<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Repositories\Article\ArticleRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;


/**
 *
 * @OA\Info(
 *     title="News Aggregator API",
 *     version="1.0.0",
 *     description="REST API for a Laravel-based news aggregator service."
 * )
 *
 * @OA\Tag(
 *     name="Articles",
 *     description="API Endpoints for managing articles"
 * )
 */
class ArticleController extends Controller
{
    public function __construct(private ArticleRepositoryInterface $articleRepository)
    {}

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Get all articles",
     *     description="Returns a list of articles with optional filters.",
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Filter by author ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filter by publication date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search articles by title, slug, or content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of articles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ArticleResource.php")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['category', 'author', 'date', 'search']);
        $perPage = $request->get('per_page', 10);

        $articles = $this->articleRepository->all($filters, $perPage);

        return ArticleResource::collection($articles)
        ->additional([
            'status' => true,
            'message' => 'Articles retrieved successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/v1/articles",
     *     tags={"Articles"},
     *     summary="Create a new article",
     *     description="Creates a new article with the provided data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreArticleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     ),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        // Logic to store a new article
        $dto = $request->toDto();
        $article = Article::updateOrCreate(
            ['url' => $dto->url],
            [
                'title'        => $dto->title,
                'slug'         => $dto->slug,
                'content'      => $dto->content,
                'author'       => $dto->author,
                'source'       => $dto->source,
                'published_at' => $dto->published_at,
            ]
        );
        if (!$article) {
            return response()->json(
                [
                    "status" => false,
                    "message" => "Failed to create article",
                ],
                422
            );
        }
        return response()->json(
            [
                "status" => true,
                "message" => "Article created successfully",
                "data" => new ArticleResource($article)
            ],
            201
        );
    }

    /**
     * Search articles based on various parameters.
     *
     * @OA\Get(
     *     path="/api/v1/articles/search",
     *     tags={"Articles"},
     *     summary="Search articles",
     *     description="Searches for articles based on title, slug, or content.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ArticleResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     * )
     */
    public function search(Request $request): JsonResponse
    {
        // Logic to search articles based on request parameters
        return response()->json(
            [
                "status" => true,
                "message" => "Search results",
            ],
            200
        );
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     tags={"Articles"},
     *     summary="Get article by ID",
     *     description="Returns a single article by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Article found",
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Article not found")
     *         )
     *     ),
     * )
     */
    public function show(string $id)
    {
        $article = $this->articleRepository->getById((int)$id);

        if ($article) {
            return new ArticleResource($article);
        }

        return response()->json([
            'status' => false,
            'message' => 'Article not found',
        ], 404);
    }
}
