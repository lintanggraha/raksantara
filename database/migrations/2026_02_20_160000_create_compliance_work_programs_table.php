<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplianceWorkProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('compliance_work_programs')) {
            return;
        }

        Schema::create('compliance_work_programs', function (Blueprint $table) {
            $table->id();
            $table->string('section', 64)->index();
            $table->string('work_id', 40)->unique();
            $table->string('program_kerja');
            $table->text('deskripsi');
            $table->text('target_sasaran_kerja');
            $table->string('kategori');
            $table->boolean('q1')->default(false);
            $table->boolean('q2')->default(false);
            $table->boolean('q3')->default(false);
            $table->boolean('q4')->default(false);
            $table->text('alignment_champion_program')->nullable();
            $table->text('alignment_risk_profile_2025')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['section', 'kategori']);
            $table->index(['section', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('compliance_work_programs')) {
            return;
        }

        Schema::table('compliance_work_programs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::dropIfExists('compliance_work_programs');
    }
}
