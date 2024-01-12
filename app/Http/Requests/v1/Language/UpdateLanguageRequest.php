<?php

namespace App\Http\Requests\v1\Language;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:2', 'max:20'],
            'code' => ['sometimes', 'string', 'min:2', 'max:4'],
            'updatedBy' => ['required', 'integer', 'exists:users,id'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'updated_by' => $this->updatedBy,
        ]);
    }
}