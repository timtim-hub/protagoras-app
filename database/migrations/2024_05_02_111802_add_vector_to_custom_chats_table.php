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
        Schema::table('custom_chats', function (Blueprint $table) {
            $table->string('vector_store')->nullable();
            $table->boolean('upload')->default(false); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('custom_chats', function (Blueprint $table) {
            $table->dropColumn('vector_store');
            $table->dropColumn('upload');
        });
    }
};
