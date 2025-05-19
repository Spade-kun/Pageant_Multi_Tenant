<?php

namespace App\Http\Requests\Tenant;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class UpdateCategoryRequest extends FormRequest
{
    private function setTenantConnection($slug)
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $databaseName = 'tenant_' . str_replace('-', '_', $tenant->slug);
        
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

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
            $slug = $this->route('slug');
            $this->setTenantConnection($slug);

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