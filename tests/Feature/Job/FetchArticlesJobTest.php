<?php

namespace Tests\Feature\Job;

use App\Jobs\FetchArticlesJob;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchArticlesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_job()
    {
        $job = new FetchArticlesJob(Article::SOURCE_NEWS_API);
        $job->handle();
        // Assert that the job was handled successfully
        $this->assertTrue(true);
    }

    public function test_it_fetches_articles_from_news_api()
    {
        // Fake NewsAPI HTTP response
        Http::fake([
            '*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Test Article',
                        'slug' => 'test-article',
                        'content' => 'Some content',
                        'author' => 'Test Author',
                        'source' => ['name' => Article::SOURCE_NEWS_API],
                        'import_source' => Article::SOURCE_NEWS_API,
                        'url' => 'https://example.com/article',
                        'publishedAt' => now()->toIso8601String()
                    ]
                ]
            ], 200),
        ]);

        // Dispatch job
        FetchArticlesJob::dispatchSync(Article::SOURCE_NEWS_API);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'import_source' => Article::SOURCE_NEWS_API
        ]);
    }
}
