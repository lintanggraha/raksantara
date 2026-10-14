<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiskidAndYearToRisksTable extends Migration
{
    public function up()
    {
        Schema::table('risks', function (Blueprint $table) {

            if (!Schema::hasColumn('risks', 'risk_id')) {
                $table->string('risk_id')->nullable()->after('id');
            }

            if (!Schema::hasColumn('risks', 'year')) {
                $table->year('year')->nullable()->after('risk_id');
            }

        });
    }

    public function down()
    {
        Schema::table('risks', function (Blueprint $table) {

            if (Schema::hasColumn('risks', 'risk_id')) {
                $table->dropColumn('risk_id');
            }

            if (Schema::hasColumn('risks', 'year')) {
                $table->dropColumn('year');
            }

        });
    }
}
