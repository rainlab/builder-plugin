<?php namespace {namespace};

use Schema;
use October\Rain\Database\Updates\Migration;

class {className} extends Migration
{
    public function up()
    {
        Schema::create('TABLE-NAME', function($table)
        {
        });
    }

    public function down()
    {
        Schema::drop('TABLE-NAME');
    }
}
