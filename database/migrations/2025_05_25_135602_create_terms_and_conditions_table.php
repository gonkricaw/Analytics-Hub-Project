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
        Schema::create('idnbi_terms_and_conditions', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('version', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('idnbi_users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['is_active', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idnbi_terms_and_conditions');
    }
};
