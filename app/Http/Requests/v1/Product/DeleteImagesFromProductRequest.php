<?php

namespace App\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteImagesFromProductRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'integer', 'exists:products_images,id'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'updated_by' => Auth::user()->id,
        ]);

        //filter the request
        $this->replace(
            $this->only([
                'updated_by',
                'images',
            ])
        );
    }
}