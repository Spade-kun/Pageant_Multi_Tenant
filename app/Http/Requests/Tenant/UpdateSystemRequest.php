<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateSystemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $isAuthorized = auth()->guard('tenant')->check() && 
               auth()->guard('tenant')->user()->role === 'owner';
        
        if (!$isAuthorized) {
            Log::warning('Unauthorized system update attempt', [
                'user' => auth()->guard('tenant')->check() ? auth()->guard('tenant')->user()->email : 'unauthenticated',
                'ip' => request()->ip()
            ]);
        }
        
        return $isAuthorized;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'version' => 'required|string|regex:/^\d+(\.\d+){0,2}(\-[a-zA-Z0-9]+)?$/' // Allow formats like 1.0.0, 1.0, 1, 1.0.0-beta
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'version.required' => 'A version is required to perform the update.',
            'version.regex' => 'The version must be in a valid versioning format (e.g., 1.0.0).'
        ];
    }
    
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::error('System update validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);
        
        parent::failedValidation($validator);
    }
    
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // If we're receiving the version through AJAX, log it
        if ($this->ajax()) {
            Log::info('Processing AJAX update request', [
                'version' => $this->input('version'),
                'ajax' => true
            ]);
        }
    }
} 