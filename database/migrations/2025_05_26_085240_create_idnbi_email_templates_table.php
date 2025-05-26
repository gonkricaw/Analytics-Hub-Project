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
        Schema::create('idnbi_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('subject');
            $table->text('body');
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['user_invitation', 'password_reset', 'welcome_user', 'notification', 'custom'])
                  ->default('custom');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('created_by_user_id');
            $table->index('type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_email_templates');
    }
};
