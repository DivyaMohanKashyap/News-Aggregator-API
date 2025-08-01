<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:articles,slug',
            'url'          => 'required|url|unique:articles,url',
            'content'      => 'nullable|string',
            'author'       => 'nullable|string|max:255',
            'source'       => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.string' => 'The slug must be a string.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'slug.unique' => 'The slug has already been taken.',
            'url.required' => 'The URL field is required.',
            'url.url' => 'The URL must be a valid URL.',
            'url.unique' => 'The URL has already been taken.',
            'content.string' => 'The content must be a string.',
            'author.string' => 'The author must be a string.',
            'author.max' => 'The author may not be greater than 255 characters.',
            'source.string' => 'The source must be a string.',
            'source.max' => 'The source may not be greater than 255 characters.',
            'published_at.date' => 'The published at must be a valid date.',
        ];
    }

    /**
     * Convert the request data to a DTO.
     *
     * @return \App\DTO\ArticleDTO
     */
    public function toDto(): \App\DTO\ArticleDTO
    {
        return new \App\DTO\ArticleDTO(
            title: $this->input('title'),
            slug: $this->input('slug'),
            url: $this->input('url'),
            content: $this->input('content') ?? null,
            author: $this->input('author') ?? null,
            source: $this->input('source') ?? null,
            import_source: $this->input('import_source') ?? Article::SOURCE_DEFAULT,
            published_at: $this->input('published_at')
        );
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $response = new JsonResponse([
            'success' => false,
            'errors' => $errors->toArray(),
        ], 422);

        throw new HttpResponseException($response);
    }
}
