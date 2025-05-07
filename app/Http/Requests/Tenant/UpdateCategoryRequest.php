<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateCategoryRequest extends FormRequest
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
            'description' => 'nullable|string',
            'percentage' => 'required|numeric|min:0.01|max:100',
            'is_active' => 'required|boolean',
            'display_order' => 'required|integer|min:0'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get the category ID from the route parameters
            $categoryId = $this->route('id');

            // Check if display order is unique (excluding current category)
            $existingOrder = DB::connection('tenant')
                ->table('categories')
                ->where('display_order', $this->display_order)
                ->where('id', '!=', $categoryId)
                ->exists();

            if ($existingOrder) {
                $validator->errors()->add('display_order', 'This display order is already taken. Please choose a different order number.');
            }

            // Calculate total percentage excluding current category and including the new percentage
            $totalExcludingCurrent = DB::connection('tenant')
                ->table('categories')
                ->where('id', '!=', $categoryId)
                ->sum('percentage');
            
            $newTotal = $totalExcludingCurrent + $this->percentage;

            if ($newTotal > 100) {
                $validator->errors()->add('percentage', 'Total percentage cannot exceed 100%. Current total (excluding this category) is ' . $totalExcludingCurrent . '%. Maximum allowed for this category is ' . (100 - $totalExcludingCurrent) . '%.');
            }
        });
    }
} 