<?php

namespace App\Http\Requests\v1\User;

use Illuminate\Support\Facades\Auth;
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
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'string', 'email'],
            'role' => ['sometimes', 'integer', 'in:0,1,2'],
            'password' => ['sometimes', 'string', 'min:8'],
            'phone' => ['sometimes', 'string'],
            'countryCode' => ['sometimes', 'string'],
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
            'updated_by' => Auth::user()->id,
        ]);

        //remove other fields form the request
        $this->replace($this->only(['name', 'email', 'role', 'password', 'phone', 'country_code', 'updated_by']));
    }
}
