<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskCommentMentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_comment_mentions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_comment_id')->constrained('risk_comments')->cascadeOnDelete();
            $table->foreignId('mentioned_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['risk_comment_id', 'mentioned_user_id'], 'risk_comment_mentions_unique');
            $table->index(['mentioned_user_id', 'created_at'], 'risk_comment_mentions_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_comment_mentions');
    }
}
