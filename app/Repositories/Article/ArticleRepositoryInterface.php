<?php

namespace App\Repositories\Article;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepositoryInterface
{
    public function all(array $filters = [], int $page, int $perPage): LengthAwarePaginator;
    public function getById(int $id): Article|null;
    public function getPersonalizedFeed(int $userId): ?LengthAwarePaginator;
}
