<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contestant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'gender',
        'bio',
        'photo',
        'representing',
        'is_active',
        'registration_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'registration_date' => 'date'
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($contestant) {
            // Validate minimum age requirement
            if ($contestant->age < 18) {
                throw new \Exception('Contestant must be at least 18 years old');
            }
        });
    }

    // Relationship with scores
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
} 