<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * Get the registrations associated with the participant.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the events the participant is registered for, through registrations.
     */
    public function events() // Note: Changed from HasManyThrough to ManyToMany for clarity
    {
        // A participant has and belongs to many events via the registrations table
        return $this->belongsToMany(Event::class, 'event_registrations');
    }
}
