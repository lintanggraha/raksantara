<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCauseAndControlToRisksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    Schema::table('risks', function (Blueprint $table) {
        $table->text('cause')->nullable();
        $table->text('existing_control')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('risks', function (Blueprint $table) {
            if (Schema::hasColumn('risks', 'existing_control')) {
                $table->dropColumn('existing_control');
            }

            if (Schema::hasColumn('risks', 'cause')) {
                $table->dropColumn('cause');
            }
        });
    }
}
