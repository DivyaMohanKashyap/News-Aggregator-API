<?php
namespace App\DTO;

use Carbon\Carbon;

class ArticleDTO
{
    public function __construct(
        public string $title,
        public string $slug,
        public ?string $content = null,
        public string $author,
        public string $source,
        public ?string $import_source,
        public string $url,
        public ?string $published_at
    ) {}

    public function publishedAtCarbon(): ?Carbon
    {
        return $this->published_at ? Carbon::parse($this->published_at) : null;
    }
}
