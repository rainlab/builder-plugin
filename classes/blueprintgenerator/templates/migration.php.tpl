<?php

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Migration for {{ name }}
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('{{ tableName }}', function(Blueprint $table) {
            {{ migrationCode|raw }}
        });
    }

    public function down()
    {
        Schema::dropIfExists('{{ tableName }}');
    }
};
