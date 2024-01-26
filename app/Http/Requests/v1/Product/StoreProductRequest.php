<?php

namespace App\Http\Requests\v1\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
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
            'price' => ['required', 'numeric', 'min:1'],
            'discountType' => ['sometimes', 'string', 'in:fixed,percentage'],
            'discountValue' => ['sometimes', 'numeric', 'min:0.25'],
            'initialQuantity' => ['required', 'integer', 'min:1'],
            'currentQuantity' => ['sometimes', 'integer', 'min:1'],
            'categoryId' => ['required', 'integer', 'exists:categories,id'],

            'translations' => ['required', 'array', 'min:1'],
            'translations.*.languageId' => ['required', 'integer', 'exists:languages,id'],
            'translations.*.name' => ['required', 'string', 'min:2', 'max:25'],
            'translations.*.description' => ['sometimes', 'string', 'min:2', 'max:250'],

            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['required', 'integer', 'exists:tags,id'],
        ];
    }
    protected function prepareForValidation()
    {

        $this->merge([
            'created_by' => Auth::user()->id,
            'category_id' => $this->categoryId,
            'initial_quantity' => $this->initialQuantity,
            'discount_type' => $this->discountType ? $this->discountType : null,
            'discount_value' => $this->discountValue ? $this->discountValue : null,
            'year' => $this->year ? $this->year : date("Y"),
            'status' => $this->status ? $this->status : 1,
        ]);

        if (!$this->has('currentQuantity')) {
            $this->merge([
                'current_quantity' => $this->initialQuantity,
            ]);
        } else {
            $this->merge([
                'current_quantity' => $this->currentQuantity,
            ]);
        }

        //filter the request
        $this->replace(
            $this->only([
                'status',
                'year',
                'price',
                'discountType',
                'discount_type',
                'discountValue',
                'discount_value',
                'initialQuantity',
                'initial_quantity',
                'currentQuantity',
                'current_quantity',
                'categoryId',
                'category_id',
                'created_by',

                'translations',
                'tags',
            ])
        );
    }
}