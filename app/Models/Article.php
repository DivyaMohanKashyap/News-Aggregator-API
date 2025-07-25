<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
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
