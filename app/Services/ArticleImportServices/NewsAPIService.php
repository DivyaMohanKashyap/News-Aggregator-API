<?php

namespace App\Services\ArticleImportServices;

use App\DTO\ArticleDTO;
use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsAPIService implements ArticleFetcherInterface
{
    public function __construct(private ArticleRepository $articleRepository)
    {}
    public function fetch(): void
    {
        if (!config('services.newsapi.key')) {
            Log::error("Skipping News API articles as the API key is not set in the configuration.");
            return;
        }

        $response = Http::get('https://newsapi.org/v2/everything', [
            'apiKey' => config('services.newsapi.key'),
            'q' => 'latest news',
            'language' => 'en',
            'from' => Carbon::now()->subDays(2)->toIso8601String(),
            'sortBy' => 'priority',
            // 'pageSize' => Article::ARTICLE_SOURCE_LIMIT
        ]);

        if ($response->ok()) {
            foreach ($response['articles'] as $data) {
                $this->articleRepository->saveArticle(new ArticleDTO(
                    title: $data['title'],
                    slug: $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
                    content: $data['content'] ?? '',
                    author: $data['author'] ?? 'Unknown',
                    source: $data['source']['name'] ?? 'News API',
                    import_source: Article::SOURCE_NEWS_API,
                    url: $data['url'],
                    published_at: $data['publishedAt']
                ));
            }
            Log::info( count($response['articles']) . " articles fetched from News API successfully.");
        } else {
            Log::error("No articles found in News API response: " . $response->body());
        }
    }
}
