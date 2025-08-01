<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'content' => $this->faker->paragraphs(3, true),
            'author' => $this->faker->name,
            'source' => $this->faker->company,
            'import_source' => $this->faker->randomElement([
                Article::SOURCE_DEFAULT,
                Article::SOURCE_NEWS_API,
                Article::SOURCE_THE_GUARDIAN,
                Article::SOURCE_NYTIMES
            ]),
            'url' => $this->faker->unique()->url,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
