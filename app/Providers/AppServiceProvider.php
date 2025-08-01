<?php

namespace App\Providers;

use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
use App\Repositories\Article\ArticleRepositoryInterface;
use App\Services\ArticleImportServices\ArticleFetcherInterface;
use App\Services\ArticleImportServices\GuardianService;
use App\Services\ArticleImportServices\NewsAPIService;
use App\Services\ArticleImportServices\NYTimesService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{


    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
        $this->app->bind(ArticleFetcherInterface::class, function ($app) {
            $source = app('source');
            return match($source) {
                Article::SOURCE_NEWS_API => new NewsAPIService(new ArticleRepository),
                Article::SOURCE_NYTIMES => new NYTimesService(new ArticleRepository),
                Article::SOURCE_THE_GUARDIAN => new GuardianService(new ArticleRepository),
                default => throw new \Exception("Invalid source: $source"),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
