<?php

namespace App\Models;

use App\Enums\CallbackRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    /** @use HasFactory<\Database\Factories\CallbackRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CallbackRequestStatus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
