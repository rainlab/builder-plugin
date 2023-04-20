<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Migration for {{ name }} Repeater Table
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('{{ tableName }}', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable()->index();
            $table->mediumText('value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('{{ tableName }}');
    }
};
