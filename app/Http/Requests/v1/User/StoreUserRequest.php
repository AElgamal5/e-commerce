<?php

namespace App\Http\Requests\v1\User;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in([0, 1, 2])],
            'password' => ['required', 'min:8'],
            'phone' => ['required', 'unique:users,phone'],

            'countryCode' => ['required'],
            'createdBy' => ['required', 'integer', 'exists:users,id'],
        ];
    }
    protected function prepareForValidation()
    {

        $this->merge([
            'created_by' => $this->createdBy,
        ]);

        $this->merge([
            'country_code' => $this->countryCode,
        ]);
    }
}
