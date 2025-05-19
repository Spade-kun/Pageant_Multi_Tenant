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
        return true; // In a real app, you might want to restrict this to admin users
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'version' => 'required|string'
        ];
    }
    
    /**
     * Manually validate the request.
     * This is useful when creating the request programmatically.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateResolved()
    {
        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $instance = $this->getValidatorInstance();
        
        if ($instance->fails()) {
            $this->failedValidation($instance);
        }
        
        $this->validated();
    }
} 