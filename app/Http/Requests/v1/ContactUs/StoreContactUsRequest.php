<?php

namespace App\Http\Requests\v1\ContactUs;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactUsRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:25'],
            'email' => ['required', 'string', 'email', 'max:35'],
            'phone' => ['required', 'string', 'max:30'],
            'countryCode' => ['required', 'string', 'min:2', 'max:7'],
            'title' => ['required', 'string', 'min:2', 'max:25'],
            'description' => ['sometimes', 'string', 'max:250'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'country_code' => $this->countryCode,
        ]);

        //filter the request
        $this->replace($this->only('name', 'email', 'phone', 'country_code', 'title', 'description', 'countryCode'));
    }
}
