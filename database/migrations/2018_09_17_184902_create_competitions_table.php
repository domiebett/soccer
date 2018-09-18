<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable(false);
            $table->string('abbrev')->nullable(true);
            $table->string('table_link')->nullable(true);
            $table->string('scores_link')->nullable(true);
            $table->string('fixtures_link')->nullable(true);
            $table->string('results_link')->nullable(true);
            $table->string('category')->nullable(true);
            $table->string('logo')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competitions');
    }
}
