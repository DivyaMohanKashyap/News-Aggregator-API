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
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 * @OA\Schema(
 *     schema="ArticleResponse",
 *     type="object",
 *     title="Article Resource",
 *     description="Represents an article in the news aggregator system.",
 *     required={"id", "title", "slug", "content", "author", "source", "news_source", "url", "published_at"},
 *     @OA\Xml(name="ArticleResponse"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         default="0",
 *         description="Unique identifier for the article"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         default="Test Article",
 *         description="Title of the article"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         default="test-article",
 *         description="Slug for the article, used in URLs"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         default="This is a test article content.",
 *         description="Content of the article"
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="string",
 *         default="John Doe",
 *         description="Author of the article"
 *     ),
 *     @OA\Property(
 *         property="source",
 *         type="string",
 *         default="BBC News",
 *         description="Source of the article, e.g., The Hindu, BBC News, etc."
 *     ),
 *     @OA\Property(
 *         property="news_source",
 *         type="string",
 *         default="News API",
 *         description="Source of the article, e.g., News API, The Guardian, NYTimes"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         default="https://example.com/test-article",
 *         description="URL of the article"
 *     ),
 *     @OA\Property(
 *         property="published_at",
 *         type="string",
 *         format="date-time",
 *         default="2023-10-01T12:00:00Z",
 *         description="Publication date and time of the article",
 *     )
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
     *     security={"bearerAuth": {}},
     *     tags={"Articles"},
     *     summary="Get all articles",
     *     description="Returns a list of articles with optional filters.",
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search articles by title, slug, or content",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default="1")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of articles per page",
     *         required=false,
     *         @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="A list of articles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ArticleResponse")
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
        $page = $request->get('page', 1);

        $articles = $this->articleRepository->all($filters, $page, $perPage);

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
     *     security={"bearerAuth": {}},
     *     tags={"Articles"},
     *     summary="Create a new article",
     *     description="Creates a new article with the provided data.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResponse")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Article created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResponse")
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
                'import_source' => $dto->import_source ?? Article::SOURCE_DEFAULT,
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
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/v1/articles/{id}",
     *     security={"bearerAuth": {}},
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
     *         description="Article retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResponse")
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
            return ArticleResource::make($article)
                ->additional([
                    'status' => true,
                    'message' => 'Article retrieved successfully',
                ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Article not found',
        ], 404);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/feed",
     *     security={"bearerAuth": {}},
     *     summary="Retrieve user news preferences",
     *     description="Retrieve user preferences for news articles.",
     *     tags={"Preferences"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "article_id"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="article_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preference successfully saved",
     *         @OA\JsonContent(ref="#/components/schemas/ArticleResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function personalizedFeed()
    {
        $articles = $this->articleRepository->getPersonalizedFeed(auth('sanctum')->id());
        if (!$articles) {
            return response()->json([
                'status' => false,
                'message' => 'No articles found for personalized feed',
            ], 404);
        }

        return response()->json(
            [
                'status' => true,
                'message' => 'Personalized feed retrieved successfully',
                'data' => ArticleResource::collection($articles)
            ],
            200
        );
    }
}
