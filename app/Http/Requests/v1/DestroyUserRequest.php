<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

class DestroyUserRequest extends FormRequest
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
            'deletedBy' => ['required', 'integer', 'exists:users,id'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'deleted_By' => $this->deletedBy,
        ]);
    }
}
