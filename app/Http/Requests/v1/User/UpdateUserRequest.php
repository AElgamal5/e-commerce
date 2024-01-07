<?php

namespace App\Http\Requests\v1\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string'],
            'email' => ['sometimes', 'required', 'email', 'unique:users,email'],
            'role' => ['sometimes', 'required', 'integer', Rule::in([0, 1, 2])],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'phone' => ['sometimes', 'required', 'string', 'unique:users,phone'],
            'countryCode' => ['sometimes', 'required', 'string'],

            'updatedBy' => ['required', 'integer', 'exists:users,id'],
        ];
    }
    protected function prepareForValidation()
    {
        if ($this->countryCode) {
            $this->merge([
                'country_code' => $this->countryCode,
            ]);
        }

        $this->merge([
            'updated_by' => $this->updatedBy,
        ]);
    }
}
