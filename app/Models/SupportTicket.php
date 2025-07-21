<?php

namespace App\Models;

use App\Enums\SupportTicketStatus;
use App\Observers\SupportTicketObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(SupportTicketObserver::class)]
class SupportTicket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'subject',
        'message',
        'status',
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
            'user_id' => 'integer',
            'status' => SupportTicketStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): Attribute
    {
        return Attribute::get(fn(): bool => $this->status === SupportTicketStatus::Open);
    }

    public function isInProgress(): Attribute
    {
        return Attribute::get(fn(): bool => $this->status === SupportTicketStatus::InProgress);
    }

    public function isResolved(): Attribute
    {
        return Attribute::get(fn(): bool => $this->status === SupportTicketStatus::Resolved);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', SupportTicketStatus::Open);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', SupportTicketStatus::InProgress);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', SupportTicketStatus::Resolved);
    }

    public function markAsOpen(): void
    {
        $this->status = SupportTicketStatus::Open;
        $this->save();
    }

    public function markAsInProgress(): void
    {
        $this->status = SupportTicketStatus::InProgress;
        $this->save();
    }

    public function markAsResolved(): void
    {
        $this->status = SupportTicketStatus::Resolved;
        $this->save();
    }
}
