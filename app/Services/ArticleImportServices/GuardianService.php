<?php

namespace App\Services\ArticleImportServices;

use App\DTO\ArticleDTO;
use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianService implements ArticleFetcherInterface
{
    public function __construct(private ArticleRepository $articleRepository)
    {}

    /**
     * Fetch articles from The Guardian API.
     *
     * @return void
     */
    public function fetch(): void
    {
        if (!config('services.guardian.key')) {
            Log::error("Guardian API key is not set in the configuration.");
            return;
        }

        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => config('services.guardian.key'),
            'show-fields' => 'all',
            'show-tags' => 'all',
            'page-size' => Article::ARTICLE_SOURCE_LIMIT,
            'order-by' => 'newest',
        ]);

        if ($response->ok()) {
            foreach ($response['response']['results'] as $data) {
                $dto = new ArticleDTO(
                    title: $data['webTitle'],
                    slug: $data['fields']['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
                    content: $data['fields']['body'] ?? '',
                    author: $data['fields']['byline'] ?? 'Unknown',
                    source: $data['source']['name'],
                    import_source: Article::SOURCE_THE_GUARDIAN,
                    url: $data['webUrl'],
                    published_at: $data['webPublicationDate']
                );
                $this->articleRepository->saveArticle($dto);
            }
        } else {
            Log::error("Failed to fetch articles from The Guardian: " . $response->body());
        }
    }
}
