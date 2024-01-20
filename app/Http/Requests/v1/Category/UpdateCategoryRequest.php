<?php

namespace App\Http\Requests\v1\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user != null && $user->tokenCan('employee');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'image' => ['sometimes', 'string', 'starts_with:data:image/jpeg;base64,data:image/jpg;base64,data:image/png;base64'],
            'updatedBy' => ['required', 'integer', 'exists:users,id'],

            'translations' => ['sometimes', 'array', 'min:1'],
            'translations.*.languageId' => ['required', 'integer', 'exists:languages,id'],
            'translations.*.name' => ['sometimes', 'string', 'min:2', 'max:25'],
            'translations.*.description' => ['sometimes', 'string', 'min:2', 'max:250'],
        ];
    }
}