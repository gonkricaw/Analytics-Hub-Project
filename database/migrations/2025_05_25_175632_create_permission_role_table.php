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
        Schema::create('idnbi_permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained('idnbi_permissions')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('idnbi_roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_permission_role');
    }
};
