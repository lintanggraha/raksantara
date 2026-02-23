<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUserApprovalColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pending_division')) {
                $table->string('pending_division')->nullable()->after('division');
            }

            if (!Schema::hasColumn('users', 'division_verified_at')) {
                $table->timestamp('division_verified_at')->nullable()->after('pending_division');
            }

            if (!Schema::hasColumn('users', 'is_superadmin')) {
                $table->boolean('is_superadmin')->default(false)->after('division_verified_at');
            }
        });

        DB::table('users')
            ->whereNotNull('division')
            ->whereNull('division_verified_at')
            ->update(['division_verified_at' => now()]);

        $configuredSuperadminEmails = array_values(array_filter(array_map(
            static function ($email): string {
                return strtolower(trim((string) $email));
            },
            (array) config('auth.superadmin_emails', [])
        )));

        if (!empty($configuredSuperadminEmails)) {
            foreach ($configuredSuperadminEmails as $email) {
                DB::table('users')
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->update([
                        'is_superadmin' => true,
                        'division_verified_at' => now(),
                    ]);
            }
        }

        // Security hardening:
        // Do not auto-promote the first user to superadmin.
        // Initial superadmin should be explicitly configured/assigned.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_superadmin')) {
                $table->dropColumn('is_superadmin');
            }

            if (Schema::hasColumn('users', 'division_verified_at')) {
                $table->dropColumn('division_verified_at');
            }

            if (Schema::hasColumn('users', 'pending_division')) {
                $table->dropColumn('pending_division');
            }
        });
    }
}
