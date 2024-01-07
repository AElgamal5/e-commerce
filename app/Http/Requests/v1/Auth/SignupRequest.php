<?php

namespace App\Http\Requests\v1\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['sometimes', Rule::in([2]),],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'countryCode' => ['required', 'string'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'country_code' => $this->countryCode,
        ]);
    }
}
