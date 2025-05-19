<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use Illuminate\Support\Facades\Config;

class UpdateEventAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
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
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'event_id' => 'required|exists:tenant.events,id',
            'contestant_ids' => 'required|array',
            'contestant_ids.*' => 'exists:tenant.contestants,id',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:tenant.categories,id',
            'status' => 'required|in:registered,confirmed,withdrawn',
            'notes' => 'nullable|string'
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
            // Get the current assignment
            $currentAssignment = DB::connection('tenant')
                ->table('event_contestant_categories')
                ->where('id', $this->route('id'))
                ->first();

            if (!$currentAssignment) {
                $validator->errors()->add('id', 'Assignment not found.');
                return;
            }

            // Check for existing assignments, excluding the current one
            if (isset($this->contestant_ids) && isset($this->category_ids) && isset($this->event_id)) {
                foreach ($this->contestant_ids as $contestantId) {
                    foreach ($this->category_ids as $categoryId) {
                        $exists = DB::connection('tenant')
                            ->table('event_contestant_categories')
                            ->where('event_id', $this->event_id)
                            ->where('contestant_id', $contestantId)
                            ->where('category_id', $categoryId)
                            ->where('id', '!=', $this->route('id'))
                            ->exists();

                        if ($exists) {
                            $validator->errors()->add('contestant_ids', "Contestant is already assigned to this category in the selected event.");
                            break 2;
                        }
                    }
                }
            }
        });
    }
} 