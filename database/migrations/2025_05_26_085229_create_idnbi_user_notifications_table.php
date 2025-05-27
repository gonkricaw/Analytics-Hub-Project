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
        Schema::create('idnbi_user_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('notification_id');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('idnbi_users')->onDelete('cascade');
            $table->foreign('notification_id')->references('id')->on('idnbi_notifications')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['user_id', 'notification_id']);
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('notification_id');
            $table->index('read_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_user_notifications');
    }
};
