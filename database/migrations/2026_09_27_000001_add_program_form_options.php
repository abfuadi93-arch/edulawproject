<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->string('platform')->nullable();
            $table->json('collaborators')->nullable();
            $table->boolean('requires_registration')->nullable();
            $table->string('registration_status')->nullable();
            $table->string('ticket_provider')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn(['platform', 'collaborators', 'requires_registration', 'registration_status', 'ticket_provider']);
        });
    }
};
