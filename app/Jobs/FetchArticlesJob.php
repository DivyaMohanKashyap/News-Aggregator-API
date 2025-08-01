<?php

namespace App\Jobs;

use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
use App\Services\ArticleImportServices\ArticleFetcherInterface;
use App\Services\ArticleImportServices\GuardianService;
use App\Services\ArticleImportServices\NewsAPIService;
use App\Services\ArticleImportServices\NYTimesService;
use App\Services\NewsFetcherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(protected string $source)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $articleRepository = app(ArticleRepository::class);
            $service = match($this->source) {
                Article::SOURCE_NEWS_API => new NewsAPIService($articleRepository),
                Article::SOURCE_THE_GUARDIAN => new GuardianService($articleRepository),
                Article::SOURCE_NYTIMES => new NYTimesService($articleRepository),
                // Add other sources as needed
                default     => throw new \Exception("Unsupported source: {$this->source}"),
            };

            $service->fetch();
        } catch (\Exception $e) {
            Log::error("Failed to fetch articles from " . request()->get('source'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Log::info("Articles fetched successfully.");
    }
}
