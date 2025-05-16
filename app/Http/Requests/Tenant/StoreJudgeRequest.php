<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class StoreJudgeRequest extends FormRequest
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
            'user_id' => 'required|exists:tenant.users,id',
            'specialty' => 'required|string|max:255',
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

            // Verify selected user has the 'user' role
            $user = DB::connection('tenant')
                ->table('users')
                ->where('id', $this->user_id)
                ->where('role', 'user')
                ->first();
                
            if (!$user) {
                $validator->errors()->add('user_id', 'Selected user must have the "user" role.');
            }
            
            // Check if user is already a judge by email
            $existingJudge = DB::connection('tenant')
                ->table('judges')
                ->where('email', $user->email)
                ->first();
                
            if ($existingJudge) {
                $validator->errors()->add('user_id', 'This user is already a judge.');
            }
        });
    }
} 