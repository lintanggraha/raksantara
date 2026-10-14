<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stable identity from the OIDC provider; bound on first SSO login.
            $table->string('oidc_subject', 255)->nullable()->unique('users_oidc_subject_unique');
            // Set when the user opts in to email one-time-code MFA.
            $table->timestamp('email_otp_enabled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_oidc_subject_unique');
            $table->dropColumn(['oidc_subject', 'email_otp_enabled_at']);
        });
    }
};
