<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use ApplicationException;
use SystemException;
use Exception;
use Lang;
use File;
use Schema;
use Str;
use Db;

/**
 * Manages plugin database tables.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DatabaseTableModel
{
    /**
     * @var boolean This property is used by the system internally.
     */
    public $exists = false;

    public $columns = [];

    public $name;

    public static function listPluginTables($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);
        $prefix = $pluginCodeObj->toDatabasePrefix();

        $connection = DB::connection();
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();

        return array_filter($tables, function($item) use($prefix) {
            return Str::startsWith($item, $prefix);
        });
    }
}