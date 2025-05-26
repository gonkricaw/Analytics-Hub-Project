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
        Schema::table('idnbi_menus', function (Blueprint $table) {
            $table->foreign('content_id')->references('id')->on('idnbi_contents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idnbi_menus', function (Blueprint $table) {
            $table->dropForeign(['content_id']);
        });
    }
};
