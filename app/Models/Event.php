<?php
// app/Models/Event.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder; // For Accessor

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_datetime',
        'end_datetime',
        'max_participants',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'max_participants' => 'integer',
    ];

    /**
     * Get the registrations for the event.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Accessor to get the current number of registered participants.
     * Example usage: $event->registered_count
     */
    protected function registeredCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->registrations()->count(),
        );
    }

     /**
     * Accessor to check if the event is full.
     * Example usage: $event->is_full
     */
    protected function isFull(): Attribute
    {
        // Use withCount in actual queries for better performance
        // This accessor is more for convenience on an already loaded model
        return Attribute::make(
            get: fn () => $this->registrations()->count() >= $this->max_participants,
        );
    }
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('start_datetime', '>', Carbon::now());
    }

    /**
     * Scope a query to only include events within a specific date range.
     * Usage: Event::inRange($startDate, $endDate)->get();
     */
    public function scopeInRange(Builder $query, Carbon $startDate, Carbon $endDate): void
    {
        $query->where(function($q) use ($startDate, $endDate) {
            // Event starts within the range
            $q->whereBetween('start_datetime', [$startDate, $endDate]);
            // Or event ends within the range
            $q->orWhereBetween('end_datetime', [$startDate, $endDate]);
            // Or event spans the entire range
            $q->orWhere(function($sub) use ($startDate, $endDate) {
                $sub->where('start_datetime', '<=', $startDate)
                    ->where('end_datetime', '>=', $endDate);
            });
        });
    }
}
