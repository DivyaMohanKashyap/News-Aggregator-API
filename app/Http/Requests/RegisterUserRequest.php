<?php

namespace App\Http\Requests;

use App\DTO\UserDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users to make this request
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:8",
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
            "name.required" => "The name field is required.",
            "email.required" => "The email field is required.",
            "email.email" => "The email must be a valid email address.",
            "email.unique" => "The email has already been taken.",
            "password.required" => "The password field is required.",
            "password.min" => "The password must be at least 8 characters.",
        ];
    }

    /**
     * Convert the request data to a DTO.
     *
     * @return \App\DTO\UserDTO
     */
    public function toDto(): UserDTO
    {
        return new UserDTO(
            name: $this->input('name'),
            email: $this->input('email'),
            password: $this->input('password')
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
