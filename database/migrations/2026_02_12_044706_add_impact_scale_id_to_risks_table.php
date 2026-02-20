<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImpactScaleIdToRisksTable extends Migration
{
    public function up()
    {
        Schema::table('risks', function (Blueprint $table) {

            $table->unsignedBigInteger('impact_scale_id')->nullable();

            $table->foreign('impact_scale_id')
                  ->references('id')
                  ->on('impact_scales')
                  ->nullOnDelete();

        });
    }

    public function down()
    {
        Schema::table('risks', function (Blueprint $table) {

            $table->dropForeign(['impact_scale_id']);
            $table->dropColumn('impact_scale_id');

        });
    }
}
