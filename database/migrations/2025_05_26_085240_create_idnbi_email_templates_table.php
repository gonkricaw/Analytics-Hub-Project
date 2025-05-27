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
            $table->text('html_content');
            $table->text('text_content')->nullable();
            $table->text('description')->nullable();
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->constrained('idnbi_users')->onDelete('cascade');
            $table->enum('type', ['invitation', 'password_reset', 'welcome', 'notification', 'general'])
                  ->default('general');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('created_by_user_id');
            $table->index('type');
            $table->index('name');
            $table->index('is_active');
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
