<?php

namespace App\Http\Requests\v1\Advertisement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DestroyAdvertisementRequest extends FormRequest
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
        return [];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'deleted_by' => Auth::user()->id,
            'deleted_at' => now(),
        ]);

        //filter the request
        $this->replace($this->only(['deleted_by', 'deleted_at']));
    }
}