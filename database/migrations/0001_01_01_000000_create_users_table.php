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
        Schema::create('idnbi_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_photo_path')->nullable();
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->boolean('temporary_password_used')->default(false);
            $table->rememberToken();
            $table->timestamps();
            
            $table->foreign('invited_by')->references('id')->on('idnbi_users')->onDelete('set null');
        });

        Schema::create('idnbi_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_users');
        Schema::dropIfExists('idnbi_sessions');
    }
};
