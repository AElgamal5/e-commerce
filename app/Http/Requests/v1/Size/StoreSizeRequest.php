<?php

namespace App\Http\Requests\v1\Size;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreSizeRequest extends FormRequest
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
            'code' => ['required', 'string', 'min:2', 'max:6'],
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'created_by' => Auth::user()->id,
        ]);

        //filter the request
        $this->replace($this->only(['created_by', 'code']));
    }
}