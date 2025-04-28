<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;


class Participant extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
    ];


    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }


    public function events() 
    {
       
        return $this->belongsToMany(Event::class, 'event_registrations');
    }
}
