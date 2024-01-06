<?php

namespace App\Http\Requests\v1;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in([0, 1, 2])],
            'password' => ['required', 'min:8'],
            'phone' => ['required'],

            'countryCode' => ['required'],
            'createdBy' => ['sometimes'],
        ];
    }
    protected function prepareForValidation()
    {
        if ($this->createdBy) {
            $this->merge([
                'created_by' => $this->createdBy,
            ]);
        }

        $this->merge([
            'country_code' => $this->countryCode,
        ]);
    }
}
