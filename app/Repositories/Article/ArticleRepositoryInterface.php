<?php

namespace App\Repositories\Article;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    public function all(array $filters = [], int $perPage): LengthAwarePaginator;
    public function getById(int $id): Article|null;
    public function search(string $query, array $filters = []): array;
    public function getPersonalizedFeed(int $userId, array $preferences = []): array;
}
