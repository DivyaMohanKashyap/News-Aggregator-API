<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    // Define constants for article sources
    const SOURCE_DEFAULT = null;
    const SOURCE_NEWS_API = 'News API';
    const SOURCE_THE_GUARDIAN = 'The Guardian';
    const SOURCE_NYTIMES = 'NYTimes';

    public static function newsSources(): array
    {
        return [
            self::SOURCE_NEWS_API,
            self::SOURCE_THE_GUARDIAN,
            self::SOURCE_NYTIMES
        ];
    }

    // Limit for the number of articles to fetch
    const ARTICLE_SOURCE_LIMIT = 20;
    protected $fillable = [
        'title',
        'slug',
        'content',
        'author',
        'source',
        'import_source',
        'url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the preferences associated with the article.
     */
    public function preferences()
    {
        return $this->hasMany(Preference::class);
    }
}
