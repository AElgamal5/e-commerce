<?php

namespace App\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isNull;

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
            'discountType' => ['sometimes', 'nullable', 'string', 'in:fixed,percentage'],
            'discountValue' => ['sometimes', 'nullable', 'numeric', 'min:0.25'],
            'initialQuantity' => ['sometimes', 'integer', 'min:1'],
            'currentQuantity' => ['sometimes', 'integer', 'min:1'],
            'categoryId' => ['sometimes', 'integer', 'exists:categories,id'],

            'translations' => ['sometimes', 'array', 'min:1'],
            'translations.*.languageId' => ['sometimes', 'integer', 'exists:languages,id'],
            'translations.*.name' => ['sometimes', 'string', 'min:2', 'max:25'],
            'translations.*.description' => ['sometimes', 'string', 'min:2', 'max:250'],

            'tags' => ['sometimes', 'array', 'min:1'],
            'tags.*' => ['required', 'integer', 'exists:tags,id'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'updated_by' => Auth::user()->id,
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