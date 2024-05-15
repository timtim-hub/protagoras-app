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
            $table->integer('plagiarism_pages')->default(0);
            $table->integer('ai_detector_pages')->default(0);
            $table->boolean('personal_chats_feature')->nullable()->default(0);
            $table->boolean('personal_templates_feature')->nullable()->default(0);
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
            $table->dropColumn('plagiarism_pages');
            $table->dropColumn('ai_detector_pages');
            $table->dropColumn('personal_chats_feature');
            $table->dropColumn('personal_templates_feature');
        });
    }
};
