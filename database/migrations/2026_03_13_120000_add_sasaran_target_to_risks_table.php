<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSasaranTargetToRisksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('risks', 'sasaran_target')) {
            return;
        }

        Schema::table('risks', function (Blueprint $table) {
            $table->text('sasaran_target')->nullable()->after('risk_title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('risks', 'sasaran_target')) {
            return;
        }

        Schema::table('risks', function (Blueprint $table) {
            $table->dropColumn('sasaran_target');
        });
    }
}

