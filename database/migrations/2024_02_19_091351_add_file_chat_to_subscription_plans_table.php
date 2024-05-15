<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('file_chat_feature')->nullable()->default(1);
            $table->boolean('video_image_feature')->nullable()->default(0);
            $table->boolean('voice_clone_feature')->nullable()->default(0);
            $table->boolean('sound_studio_feature')->nullable()->default(0);
            $table->float('chat_word_file_size')->nullable()->default(1);
            $table->integer('voice_clone_number')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('file_chat_feature');
            $table->dropColumn('chat_word_file_size');
            $table->dropColumn('voice_clone_number');
            $table->dropColumn('video_image_feature');
            $table->dropColumn('voice_clone_feature');
            $table->dropColumn('sound_studio_feature');
        });
    }
};
