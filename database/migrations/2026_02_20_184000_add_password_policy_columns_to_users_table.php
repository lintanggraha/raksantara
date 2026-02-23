<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPasswordPolicyColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'force_password_change')) {
                $table->boolean('force_password_change')->default(false)->after('is_superadmin');
            }

            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('force_password_change');
            }
        });

        DB::table('users')
            ->whereNull('password_changed_at')
            ->update([
                'password_changed_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_changed_at')) {
                $table->dropColumn('password_changed_at');
            }

            if (Schema::hasColumn('users', 'force_password_change')) {
                $table->dropColumn('force_password_change');
            }
        });
    }
}

