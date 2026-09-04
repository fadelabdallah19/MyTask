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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_enabled')->default(true)->after('password');
            $table->boolean('reminder_24h')->default(true)->after('email_enabled');
            $table->boolean('reminder_1h')->default(true)->after('reminder_24h');
            $table->string('whatsapp_number')->nullable()->after('reminder_1h');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_enabled', 'reminder_24h', 'reminder_1h', 'whatsapp_number']);
        });
    }
};
