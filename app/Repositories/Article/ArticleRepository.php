<?php

namespace App\Repositories\Article;

use App\DTO\ArticleDTO;
use App\Models\Article;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleRepository implements ArticleRepositoryInterface
{

    public function saveArticle(ArticleDTO $dto): Article
    {
        return Article::updateOrCreate(
            ['slug' => $dto->slug, 'source' => $dto->source],
            [
                'title' => $dto->title,
                'content' => $dto->content,
                'author' => $dto->author,
                'source' => $dto->source,
                'import_source' => $dto->import_source,
                'url' => $dto->url,
                'published_at' => Carbon::parse($dto->published_at)->format('Y-m-d H:i:s')
            ]
        );
    }

    public function all(array $filters = [], int $page, int $perPage = 10): LengthAwarePaginator
    {
        $query = Article::query();
        // Apply filters if provided
        if (!empty($filters)) {
            $query
            ->when(isset($filters['author']), fn($q) => $q->where('author_id', $filters['author']))
            ->when(isset($filters['date']), fn($q) => $q->whereDate('created_at', $filters['date']))
            ->when(isset($filters['search']), function ($q) use ($filters) {
                $q->where(function ($query) use ($filters) {
                    $searchInput = $filters['search'];
                    $query->where('title', 'like', "%{$searchInput}%")
                          ->orWhere('slug', 'like', "%{$searchInput}%")
                          ->orWhere('content', 'like', "%{$searchInput}%");
                });
            })->orderBy('published_at', 'desc');
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getById(int $id): Article|null
    {
        return Article::find($id) ?? null;
    }


    public function getPersonalizedFeed(int $userId): ?LengthAwarePaginator
    {
        if (!$userId) {
            throw new \Exception('User ID is required');
        }
        // Fetch user preferences
        try {
            $user = User::find($userId);
            $preference = $user->preference;

            return Article::when($preference, function ($query) use ($preference) {
                if ($preference->category) {
                    $query->where('category', $preference->category);
                }
                if ($preference->source) {
                    $query->where('source', $preference->source);
                }
            })->latest()->paginate(10);
        } catch (\Exception $error) {
            throw new \Exception("Error fetching personalized feed: " . $error->getMessage());
        }
    }
}
