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
        Schema::create('idnbi_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->enum('type', ['list_menu', 'content_menu']);
            $table->string('icon')->nullable()->comment('Font Awesome class');
            $table->string('route_or_url')->nullable();
            $table->unsignedBigInteger('content_id')->nullable();
            $table->integer('order')->default(0);
            $table->json('role_permissions_required')->nullable();
            $table->timestamps();

            // Foreign key constraints (parent_id self-reference)
            $table->foreign('parent_id')->references('id')->on('idnbi_menus')->onDelete('cascade');
            
            // Indexes
            $table->index('parent_id');
            $table->index('content_id');
            $table->index('order');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_menus');
    }
};
