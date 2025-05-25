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
        Schema::create('idnbi_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'create-users', 'edit-content', 'view-reports'
            $table->string('display_name'); // e.g., 'Create Users', 'Edit Content', 'View Reports'
            $table->text('description')->nullable();
            $table->string('group')->nullable(); // e.g., 'users', 'content', 'reports'
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_permissions');
    }
};
