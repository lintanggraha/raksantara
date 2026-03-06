<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOjkMitigationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ojk_mitigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_number', 40)->nullable();
            $table->text('action_plan');
            $table->string('pic', 255)->nullable();
            $table->text('solution')->nullable();
            $table->text('target')->nullable();
            $table->date('target_date')->nullable();
            $table->string('mitigation_status', 32)->default('On Progress');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['sort_order', 'id'], 'ojk_mitigation_sort_idx');
            $table->index('target_date', 'ojk_mitigation_target_date_idx');
            $table->index('mitigation_status', 'ojk_mitigation_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ojk_mitigation_items');
    }
}
