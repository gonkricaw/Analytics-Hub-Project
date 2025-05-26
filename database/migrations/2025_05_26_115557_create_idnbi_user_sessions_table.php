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
        Schema::create('idnbi_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('idnbi_users')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->timestamp('last_activity_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['login_at']);
            $table->index(['last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_user_sessions');
    }
};
