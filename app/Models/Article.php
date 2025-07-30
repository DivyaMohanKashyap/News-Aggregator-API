<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    const ARTICLE_SOURCE_LIMIT = 20;
    protected $fillable = [
        'title',
        'slug',
        'content',
        'author',
        'source',
        'url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
