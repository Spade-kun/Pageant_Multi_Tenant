<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'interval' => 'required|in:3_days,15_days,monthly,yearly',
            'max_events' => 'required|integer|min:0',
            'max_contestants' => 'required|integer|min:0',
            'max_categories' => 'required|integer|min:0',
            'max_judges' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'analytics' => 'boolean',
            'support_priority' => 'boolean',
            'is_active' => 'boolean',
            'dashboard_access' => 'boolean',
            'user_management' => 'boolean',
            'subscription_management' => 'boolean',
            'pageant_management' => 'boolean',
            'reports_module' => 'boolean',
        ];
    }
} 