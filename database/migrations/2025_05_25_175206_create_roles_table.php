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
        Schema::create('idnbi_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'admin', 'manager', 'sales_user'
            $table->string('display_name'); // e.g., 'Administrator', 'Manager', 'Sales User'
            $table->text('description')->nullable();
            $table->string('color')->default('#007BFF'); // Default blue color
            $table->boolean('is_system')->default(false); // System roles cannot be deleted
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_roles');
    }
};
