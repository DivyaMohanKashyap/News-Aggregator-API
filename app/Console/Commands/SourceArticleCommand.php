<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SourceArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to source articles from news api platforms.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Fetching articles...");

        try {
            $this->fetchFromNewsApi();
            $this->fetchFromGuardian();
            $this->fetchFromNYTimes();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }

        $this->info("Done.");
    }

    protected function fetchFromNewsApi()
    {
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => config('services.newsapi.key'),
            'language' => 'en',
        ]);

        if ($response->ok()) {
            foreach ($response['articles'] as $data) {
                $this->saveArticle([
                    'title' => $data['title'],
                    'content' => $data['content'] ?? '',
                    'author' => $data['author'] ?? 'Unknown',
                    'source' => $data['source']['name'],
                    'url' => $data['url'],
                    'published_at' => $data['publishedAt'],
                ]);
            }
            $this->info( count($response['articles']) . " articles fetched from News API successfully.");
        } else {
            $this->error("No articles found in News API response: " . $response->body());
        }
    }

    protected function fetchFromGuardian()
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => config('services.guardian.key'),
            'show-fields' => 'all',
        ]);

        if ($response->ok()) {
            foreach ($response['response']['results'] as $data) {
                $this->saveArticle([
                    'title' => $data['webTitle'],
                    'content' => $data['fields']['body'] ?? '',
                    'author' => $data['fields']['byline'] ?? 'Unknown',
                    'source' => 'The Guardian',
                    'url' => $data['webUrl'],
                    'published_at' => $data['webPublicationDate'],
                ]);
            }
        } else {
            $this->error("Failed to fetch articles from The Guardian: " . $response->body());
        }
    }

    protected function fetchFromNYTimes()
    {
        $response = Http::get('https://api.nytimes.com/svc/topstories/v2/home.json', [
            'api-key' => config('services.nytimes.key'),
        ]);

        if ($response->ok()) {
            foreach ($response['results'] as $data) {
                $this->saveArticle([
                    'title' => $data['title'],
                    'content' => $data['abstract'],
                    'author' => $data['byline'] ?? 'Unknown',
                    'source' => 'NYTimes',
                    'url' => $data['url'],
                    'published_at' => $data['published_date'],
                ]);
            }
        } else {
            $this->error("Failed to fetch articles from NY Times: " . $response->body());
        }
    }

    protected function saveArticle($data)
    {
        try {
            Article::updateOrCreate(
                ['url' => $data['url']],
                [
                    'title' => $data['title'],
                    'slug' => Str::slug($data['title']),
                    'content' => $data['content'],
                    'author' => $data['author'],
                    'source' => $data['source'],
                    'published_at' => $data['published_at'] ? \Carbon\Carbon::parse($data['published_at']) : null,
                ]
            );
        } catch (\Exception $e) {
            $this->error("Failed to save article: " . $e->getMessage());
        }
    }
}
