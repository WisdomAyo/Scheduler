<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade'); // Foreign key to events
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade'); // Foreign key to participants
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps(); // created_at, updated_at

            // Prevent a participant from registering for the same event twice
            $table->unique(['event_id', 'participant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
