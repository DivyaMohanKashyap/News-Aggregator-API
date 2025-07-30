<?php

namespace Tests\Feature\Api;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Str;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_articles()
    {
        // Create user and authenticate using Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        Article::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_fetch_article_by_id()
    {
        // Create user and authenticate using Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $article = Article::factory()->create();

        $response = $this->getJson('/api/v1/articles/' . $article->id);

        $response->assertStatus(200)->assertJsonFragment(['title' => $article->title]);
    }

    public function test_search_articles()
    {
        // Create user and authenticate using Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $title = 'Unique Article Title';

        Article::factory()->create([
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => "This is a unique article content.",
            'author' => "John Doe",
            'source' => "Example News",
            'url' => 'https://example.com/unique-article',
            'published_at' => now()

        ]);

        $response = $this->getJson('/api/v1/articles?search=Unique');

        $response->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_authenticated_user_can_create_article()
    {
        // Create user and authenticate using Sanctum
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $token = $user->createToken('TestToken')->plainTextToken;

        $title = 'New Article Title';
        $response = $this->postJson('/api/v1/articles', [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => "This is a unique article content.",
            'author' => "John Doe",
            'source' => "Example News",
            'url' => 'https://example.com/unique-article',
            'published_at' => now()
        ]);

        $response->assertStatus(201);
    }
}
