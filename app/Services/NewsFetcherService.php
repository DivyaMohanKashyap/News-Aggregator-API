<?php

namespace App\Services;

use App\DTO\ArticleDTO;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsFetcherService
{
    public function fetchFromNewsApi()
    {
        if (!config('services.newsapi.key')) {
            Log::error("News API key is not set in the configuration.");
            return;
        }

        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => config('services.newsapi.key'),
            'language' => 'en',
            'from' => Carbon::now()->subDays(1)->toIso8601String(),
            'sortBy' => 'priority',
            'pageSize' => Article::ARTICLE_SOURCE_LIMIT
        ]);

        if ($response->ok()) {
            foreach ($response['articles'] as $data) {
                $dto = new ArticleDTO(
                    title: $data['title'],
                    slug: $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
                    content: $data['content'] ?? '',
                    author: $data['author'] ?? 'Unknown',
                    source: $data['source']['name'] ?? 'News API',
                    url: $data['url'],
                    published_at: $data['publishedAt']
                );
                $this->saveArticle($dto);
            }
            Log::info( count($response['articles']) . " articles fetched from News API successfully.");
        } else {
            Log::error("No articles found in News API response: " . $response->body());
        }
    }

    public function fetchFromGuardian()
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
                    source: $data['source']['name'] ?? 'The Guardian',
                    url: $data['webUrl'],
                    published_at: $data['webPublicationDate']
                );
                $this->saveArticle($dto);
            }
        } else {
            Log::error("Failed to fetch articles from The Guardian: " . $response->body());
        }
    }

    public function fetchFromNYTimes()
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
                $dto = new ArticleDTO(
                    title: $data['title'],
                    slug: $data['fields']['slug'] ?? \Illuminate\Support\Str::slug($data['title']),
                    content: $data['abstract'] ?? '',
                    author: $data['byline'] ?? 'Unknown',
                    source: $data['source'] ?? 'NYTimes',
                    url: $data['url'],
                    published_at: $data['published_date']
                );
                $this->saveArticle($dto);
            }
        } else {
            Log::error("Failed to fetch articles from NY Times: " . $response->body());
        }
    }

    private function saveArticle(ArticleDTO $dto)
    {
        try {
            /** @var ArticleDTO $dto */
            Article::updateOrCreate(
                ['url' => $dto->url],
                [
                    'title'        => $dto->title,
                    'slug'         => $dto->slug ?? \Illuminate\Support\Str::slug($dto->title),
                    'content'      => $dto->content,
                    'author'       => $dto->author,
                    'source'       => $dto->source,
                    'published_at' => $dto->publishedAtCarbon(),
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to save article: " . $e->getMessage());
        }
    }
}
