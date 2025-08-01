<?php

namespace App\Services\ArticleImportServices;

use App\DTO\ArticleDTO;
use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NYTimesService implements ArticleFetcherInterface
{
    public function __construct(private ArticleRepository $articleRepository)
    {}
    public function fetch(): void
    {
        if (!config('services.nytimes.key')) {
            Log::error("NY Times API key is not set in the configuration.");
            return;
        }

        $response = Http::get('https://api.nytimes.com/svc/topstories/v2/home.json', [
            'api-key' => config('services.nytimes.key'),
            'section' => 'home',
            'language' => 'en',
            'sort' => 'newest',
            'pageSize' => Article::ARTICLE_SOURCE_LIMIT
        ]);

        if ($response->ok()) {
            foreach ($response['results'] as $data) {
                $this->articleRepository->saveArticle(new ArticleDTO(
                    title: $data['title'],
                    slug: $data['fields']['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
                    content: $data['abstract'] ?? '',
                    author: $data['byline'] ?? 'Unknown',
                    source: $data['source'],
                    import_source: Article::SOURCE_NYTIMES,
                    url: $data['url'],
                    published_at: $data['published_date']
                ));
            }
        } else {
            Log::error("Failed to fetch articles from NY Times: " . $response->body());
        }
    }
}
