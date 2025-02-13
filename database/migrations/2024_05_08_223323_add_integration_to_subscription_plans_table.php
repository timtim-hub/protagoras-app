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
            $table->boolean('integration_feature')->nullable()->default(0);
            $table->boolean('personal_claude_api')->default(false)->nullable();
            $table->boolean('personal_gemini_api')->default(false)->nullable();
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
            $table->dropColumn('integration_feature');
            $table->dropColumn('personal_claude_api');
            $table->dropColumn('personal_gemini_api');
        });
    }
};
