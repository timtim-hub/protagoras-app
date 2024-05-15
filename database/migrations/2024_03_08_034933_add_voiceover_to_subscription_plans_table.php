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
            $table->string('voiceover_vendors')->nullable();
            $table->boolean('brand_voice_feature')->nullable()->default(0);
            $table->integer('file_result_duration')->default(-1);
            $table->integer('document_result_duration')->default(-1);
            $table->integer('dalle_images')->default(0);
            $table->integer('sd_images')->default(0);
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
            $table->dropColumn('voiceover_vendors');
            $table->dropColumn('brand_voice_feature');
            $table->dropColumn('file_result_duration');
            $table->dropColumn('document_result_duration');
            $table->dropColumn('dalle_images');
            $table->dropColumn('sd_images');
        });
    }
};
