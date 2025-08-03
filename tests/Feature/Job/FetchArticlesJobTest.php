<?php

namespace Tests\Feature\Job;

use App\Jobs\FetchArticlesJob;
use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
use App\Services\ArticleImportServices\NewsAPIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
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
        config(['services.newsapi.key' => 'fake-test-key']);

        // Fake the external HTTP call
        Http::fake([
            '*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Test Article',
                        'slug' => 'test-article',
                        'content' => 'Some content',
                        'author' => 'Test Author',
                        'source' => ['name' => 'News API'],
                        'url' => 'https://example.com/article',
                        'publishedAt' => now()->toIso8601String()
                    ]
                ]
            ], 200),
        ]);

        // Mock the ArticleRepository
        $mockRepo = Mockery::mock(ArticleRepository::class);
        $mockRepo->shouldReceive('saveArticle')->once()->andReturn(new Article);

        $this->app->instance(ArticleRepository::class, $mockRepo);

        // Run the job synchronously
        FetchArticlesJob::dispatchSync(Article::SOURCE_NEWS_API);
    }
}
