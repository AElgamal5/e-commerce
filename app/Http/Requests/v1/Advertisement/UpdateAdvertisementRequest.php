<?php

namespace App\Http\Requests\v1\Advertisement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvertisementRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:2', 'max:250'],
            'image' => ['sometimes', 'string', 'starts_with:data:image/jpeg;base64,data:image/jpg;base64,data:image/png;base64'],
            'link' => ['sometimes', 'string', 'url', 'max:250'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'updated_by' => Auth::user()->id,
        ]);

        //filter the request
        $this->replace($this->only(['updated_by', 'name', 'image', 'link', 'status']));
    }
}