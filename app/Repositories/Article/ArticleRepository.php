<?php

namespace App\Repositories\Article;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function all(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Article::query();

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
            });
        }

        return $query->latest()->paginate(10);
    }

    public function getById(int $id): Article|null
    {
        return Article::find($id) ?? null;
    }

    public function search(string $query, array $filters = []): array
    {
        // Logic to search articles based on a query and filters
        return [];
    }

    public function getPersonalizedFeed(int $userId, array $preferences = []): array
    {
        // Logic to fetch personalized feed for a user based on preferences
        return [];
    }
}
