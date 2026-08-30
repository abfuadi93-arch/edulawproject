<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->time('event_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('event_timezone')->nullable();
            $table->string('event_status')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_region')->nullable();
            $table->string('venue_postal_code', 20)->nullable();
            $table->string('venue_country', 2)->nullable();
            $table->string('online_url')->nullable();
            $table->string('ticket_currency', 3)->nullable();
            $table->string('ticket_availability')->nullable();
            $table->dateTime('registration_opens_at')->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('organizer_url')->nullable();
            $table->string('organizer_type')->nullable();
            $table->json('gallery_images')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'event_time', 'end_time', 'event_timezone', 'event_status',
                'venue_address', 'venue_city', 'venue_region', 'venue_postal_code',
                'venue_country', 'online_url', 'ticket_currency', 'ticket_availability',
                'registration_opens_at', 'organizer_name', 'organizer_url',
                'organizer_type', 'gallery_images',
            ]);
        });
    }
};
