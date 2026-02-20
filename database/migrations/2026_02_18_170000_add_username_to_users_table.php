<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUsernameToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        DB::table('users')
            ->select('id', 'name', 'email')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $base = (string) Str::of($user->email ?: $user->name ?: ('user' . $user->id))
                        ->before('@')
                        ->replaceMatches('/[^A-Za-z0-9_.-]/', '')
                        ->lower();

                    if ($base === '') {
                        $base = 'user' . $user->id;
                    }

                    $username = $base;

                    if (DB::table('users')->where('username', $username)->where('id', '<>', $user->id)->exists()) {
                        $username = $base . '_' . $user->id;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['username' => $username]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_username_unique');
            $table->dropColumn('username');
        });
    }
}
