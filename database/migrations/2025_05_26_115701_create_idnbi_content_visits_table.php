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
        Schema::create('idnbi_content_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('idnbi_users')->onDelete('cascade');
            $table->foreignId('content_id')->nullable()->constrained('idnbi_contents')->onDelete('cascade');
            $table->foreignId('menu_id')->nullable()->constrained('idnbi_menus')->onDelete('cascade');
            $table->string('page_type')->default('content'); // 'content', 'menu', 'dashboard'
            $table->string('page_title')->nullable();
            $table->string('page_url');
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at');
            $table->integer('duration_seconds')->default(0); // Time spent on page
            $table->timestamps();
            
            $table->index(['user_id', 'visited_at']);
            $table->index(['content_id', 'visited_at']);
            $table->index(['menu_id', 'visited_at']);
            $table->index(['page_type', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_content_visits');
    }
};
