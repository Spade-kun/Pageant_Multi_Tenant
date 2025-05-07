<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow tenant owners to perform updates
        return auth()->guard('tenant')->check() && 
               auth()->guard('tenant')->user()->role === 'owner';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'version' => 'required|string|regex:/^\d+\.\d+\.\d+$/' // Ensure format like 1.0.0
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'version.required' => 'A version is required to perform the update.',
            'version.regex' => 'The version must be in a valid semantic versioning format (e.g., 1.0.0).'
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated(): array
    {
        return $this->validate($this->rules());
    }
} 