<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasFactory;

    // No mass assignment protection needed if created via relationships or explicitly
    // but can add fillable if needed for other scenarios
    protected $fillable = [
      'event_id',
      'participant_id',
      'registered_at', // Usually set automatically
    ];


    protected $casts = [
        'registered_at' => 'datetime',
    ];

    /**
     * Get the event associated with the registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the participant associated with the registration.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
