<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UiSettings extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'logo_header_color',
        'navbar_color',
        'sidebar_color',
        'navbar_position',
        'sidebar_position',
        'is_sidebar_collapsed',
        'is_navbar_fixed',
        'is_sidebar_fixed'
    ];

    protected $casts = [
        'is_sidebar_collapsed' => 'boolean',
        'is_navbar_fixed' => 'boolean',
        'is_sidebar_fixed' => 'boolean'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
} 