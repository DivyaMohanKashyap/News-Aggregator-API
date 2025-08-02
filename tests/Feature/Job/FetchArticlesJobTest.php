<?php

namespace Tests\Feature\Job;

use App\Jobs\FetchArticlesJob;
use App\Models\Article;
use App\Repositories\Article\ArticleRepository;
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
        Http::fake([
            '*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Test Article',
                        'slug' => 'test-article',
                        'content' => 'Some content',
                        'author' => 'Test Author',
                        'source' => ['name' => Article::SOURCE_NEWS_API],
                        'url' => 'https://example.com/article',
                        'publishedAt' => now()->toIso8601String()
                    ]
                ]
            ], 200),
        ]);

        $mockRepo = Mockery::mock(ArticleRepository::class);
        $mockRepo->shouldReceive('saveArticle')->once()->withArgs(static fn($dto) =>
            $dto->title === 'Test Article'
            && $dto->url === 'https://example.com/article'
            && $dto->import_source === Article::SOURCE_NEWS_API
        );


        $this->app->instance(ArticleRepository::class, $mockRepo);

        FetchArticlesJob::dispatchSync(Article::SOURCE_NEWS_API);
    }
}
