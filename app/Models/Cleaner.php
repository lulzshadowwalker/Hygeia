<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cleaner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_area',
        'available_days',
        'max_hours_per_week',
        'time_slots',
        'years_of_experience',
        'has_cleaning_supplies',
        'comfortable_with_pets',
        'previous_job_types',
        'service_radius',
        'preferred_job_types',
        'agreed_to_terms',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'available_days' => 'array',
            'time_slots' => 'array',
            'has_cleaning_supplies' => 'boolean',
            'comfortable_with_pets' => 'boolean',
            'previous_job_types' => 'array',
            'preferred_job_types' => 'array',
            'agreed_to_terms' => 'boolean',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
