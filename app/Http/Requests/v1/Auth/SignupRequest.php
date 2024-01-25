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
            'email' => ['required', 'email'],
            'role' => ['sometimes', 'in:2'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required', 'string'],
            'countryCode' => ['required', 'string'],
        ];
    }
    protected function prepareForValidation(): void
    {
        $this->merge([
            'country_code' => $this->countryCode,
        ]);

        $this->replace($this->only(['name', 'email', 'role', 'password', 'phone', 'country_code', 'countryCode']));
    }

    public function messages(): array
    {
        return [
            'name.required' => __('nameIsRequired'),

            'email.required' => __('emailIsRequired'),
            'email.email' => __('emailIsNotValid'),

            'password.required' => __('passwordIsRequired'),
            'password.min' => __('passwordMustBeAtLeast8Characters'),

            'role.in' => __('roleMustBeAtAsACustomer'),

            'phone.required' => __('phoneIsRequired'),

            'countryCode.required' => __('countryCodeIsRequired'),
        ];
    }
}
