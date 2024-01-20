<?php

namespace App\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        $year = date("Y");

        return [
            'status' => ['sometimes', 'integer', 'in:0,1'],
            'year' => ['sometimes', 'integer', 'min:2020', "max:$year"],
            'price' => ['sometimes', 'numeric', 'min:1'],
            'discountType' => ['sometimes', 'string', 'in:fixed,percentage'],
            'discountValue' => ['sometimes', 'numeric', 'min:0.25'],
            'initialQuantity' => ['sometimes', 'integer', 'min:1'],
            'currentQuantity' => ['sometimes', 'integer', 'min:1'],
            'categoryId' => ['sometimes', 'integer', 'exists:categories,id'],
            'updatedBy' => ['sometimes', 'integer', 'exists:users,id'],

            'translations' => ['sometimes', 'array', 'min:1'],
            'translations.*.languageId' => ['sometimes', 'integer', 'exists:languages,id'],
            'translations.*.name' => ['sometimes', 'string', 'min:2', 'max:25'],
            'translations.*.description' => ['sometimes', 'string', 'min:2', 'max:250'],
        ];
    }
    protected function prepareForValidation()
    {

        $this->merge([
            'updated_by' => $this->updatedBy,
        ]);

        if ($this->categoryId) {
            $this->merge([
                'category_id' => $this->categoryId,
            ]);
        }
        if ($this->discountType) {
            $this->merge([
                'discount_type' => $this->discountType,
            ]);
        }
        if ($this->discountValue) {
            $this->merge([
                'discount_value' => $this->discountValue,
            ]);
        }
        if ($this->initialQuantity) {
            $this->merge([
                'initial_quantity' => $this->initialQuantity,
            ]);
        }
        if ($this->currentQuantity) {
            $this->merge([
                'current_quantity' => $this->currentQuantity,
            ]);
        }
    }
}