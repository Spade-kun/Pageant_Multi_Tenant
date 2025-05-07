<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class StoreCategoryRequest extends FormRequest
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
            'percentage' => 'required|numeric|min:1|max:100',
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
            // Get the tenant slug from the route parameters
            $slug = $this->route('slug');
            
            // Set up tenant database connection
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

            // Check if display order is unique
            $existingOrder = DB::connection('tenant')
                ->table('categories')
                ->where('display_order', $this->display_order)
                ->exists();

            if ($existingOrder) {
                $validator->errors()->add('display_order', 'This display order is already taken. Please choose a different order number.');
            }

            // Calculate total percentage including the new category
            $currentTotal = DB::connection('tenant')
                ->table('categories')
                ->sum('percentage');
            
            $newTotal = $currentTotal + $this->percentage;

            if ($newTotal > 100) {
                $validator->errors()->add('percentage', 'Total percentage cannot exceed 100%. Current total is ' . $currentTotal . '%. Maximum allowed for this category is ' . (100 - $currentTotal) . '%.');
            }
        });
    }
} 