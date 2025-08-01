<?php

namespace App\Http\Resources;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ArticleResource",
 *     type="object",
 *     title="Article Resource",
 *     description="Represents an article in the news aggregator system.",
 *     required={"id", "title", "slug", "content", "author", "source", "news_source", "url", "published_at"},
 *     @OA\Xml(name="ArticleResource"),
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Unique identifier for the actor type"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="slug",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="content",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="source",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="news_source",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         description="Name of the actor type"
 *     ),
 *     @OA\Property(
 *         property="published_at",
 *         type="string",
 *         description="Name of the actor type"
 *     )
 * )
 */
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
            'import_source' => $this->resource->import_source ?? Article::SOURCE_DEFAULT,
            'url'          => $this->resource->url,
            'published_at' => $this->resource->published_at ?
                Carbon::parse($this->resource->published_at)->format('Y-m-d H:i:s') :
                null,
            'created_at'   => $this->resource->created_at->format('Y-m-d H:i:s')
        ];
    }
}
