<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title'        => $this->resource->title,
            'slug'         => $this->resource->slug,
            'author'       => $this->resource->author,
            'content'      => $this->resource->content,
            'source'       => $this->resource->source,
            'url'          => $this->resource->url,
            'published_at' => $this->resource->published_at ?
                Carbon::parse($this->resource->published_at)->format('Y-m-d H:i:s') :
                null,
            'created_at'   => $this->resource->created_at->format('Y-m-d H:i:s')
        ];
    }
}
