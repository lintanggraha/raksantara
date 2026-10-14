<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRadarGroupsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('radar_groups')) {
            Schema::create('radar_groups', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('description');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('radar_groups');
    }
}
