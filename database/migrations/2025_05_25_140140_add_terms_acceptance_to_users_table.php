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
        Schema::table('idnbi_users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('temporary_password_used');
            $table->string('terms_accepted_version')->nullable()->after('terms_accepted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idnbi_users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_at', 'terms_accepted_version']);
        });
    }
};
