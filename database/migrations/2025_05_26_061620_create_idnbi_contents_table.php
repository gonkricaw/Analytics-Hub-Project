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
        Schema::create('idnbi_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('type', ['custom', 'embed_url']);
            $table->text('custom_content')->nullable()->comment('HTML content from text editor');
            $table->text('embed_url_original')->nullable()->comment('Original URL for embed type');
            $table->uuid('embed_url_uuid')->nullable()->unique()->comment('UUID for secure embed access');
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('updated_by_user_id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('created_by_user_id')->references('id')->on('idnbi_users')->onDelete('cascade');
            $table->foreign('updated_by_user_id')->references('id')->on('idnbi_users')->onDelete('cascade');
            
            // Indexes
            $table->index('type');
            $table->index('slug');
            $table->index('embed_url_uuid');
            $table->index('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_contents');
    }
};
