<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Migration for {{ name }} Join Table
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('{{ tableName }}', function(Blueprint $table) {
            $table->integer('{{ parentKey }}')->unsigned();
            $table->integer('{{ relatedKey }}')->unsigned();
            $table->primary(['{{ parentKey }}', '{{ relatedKey }}']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('{{ tableName }}');
    }
};
