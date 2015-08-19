<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use ApplicationException;
use ValidationException;
use SystemException;
use Exception;
use Validator;
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
class DatabaseTableModel extends BaseModel
{
    public $columns = [];

    /**
     * @var string Specifies the database table model
     */
    public $name;

    protected static $fillable = [
        'name',
        'columns'
    ];

    protected $validationRules = [
        'name' => ['required', 'regex:/^[a-z]+[a-z0-9_]+$/', 'tablePrefix']
    ];

    /**
     * @var Doctrine\DBAL\Schema\Table Table details loaded from the database.
     */
    protected $tableInfo;

    /**
     * @var string Contains a database prefix of the plugin the model belongs to.
     */
    protected $pluginDbPrefix;

    /**
     * @var Doctrine\DBAL\Schema\AbstractSchemaManager Contains the database schema
     */
    protected static $schemaManager = null;

    /**
     * @var Doctrine\DBAL\Schema\Schema Contains the database schema
     */
    protected static $schema = null;

    public static function listPluginTables($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);
        $prefix = $pluginCodeObj->toDatabasePrefix();

        $tables = self::getSchemaManager()->listTableNames();

        return array_filter($tables, function($item) use($prefix) {
            return Str::startsWith($item, $prefix);
        });
    }

    public static function tableExists($name)
    {
        return self::getSchema()->hasTable($name);
    }

    /**
     * Loads the table from the database.
     * @param string $name Specifies the table name.
     */
    public function load($name)
    {
        if (!self::tableExists($name)) {
            throw new SystemException(sprintf('The table with name %s doesn\'t exist', $name));
        }

        $schema = self::getSchemaManager()->createSchema();

        $this->name = $name;

        $this->tableInfo = $schema->getTable($this->name);

        $this->exists = true;

        // TODO: populate the name and columns fields
    }

    public function setPluginPrefix($prefix)
    {
        $this->pluginDbPrefix = $prefix;
    }

    public function validate()
    {
        if (!strlen($this->pluginDbPrefix)) {
            throw new SystemException('Error saving the table model - the plugin database prefix is not set for the object.');
        }

        $prefix = $this->pluginDbPrefix.'_';

        $this->validationMessages = [
            'name.table_prefix' => Lang::get('rainlab.builder::lang.database.error_table_name_invalid_prefix', [
                'prefix' => $prefix
            ]),
            'name.regex' => Lang::get('rainlab.builder::lang.database.error_table_name_invalid_characters')
        ];

        Validator::extend('tablePrefix', function($attribute, $value, $parameters) use ($prefix) {
            $value = trim($value);

            if (!Str::startsWith($value, $prefix)) {
                return false;
            }

            return true;
        });

        $this->validateColumns();

        return parent::validate();
    }

    public function generateCreateOrUpdateMigration()
    {
        $schemaCreator = new DatabaseTableSchemaCreator();
        $newSchema = $schemaCreator->createTableSchema($this->name, $this->columns);

        $codeGenerator = new TableMigrationCodeGenerator();

        $migration = new MigrationModel();
        $migration->code = $codeGenerator->createOrUpdateTable($newSchema, null);

        return $migration;
    }

    protected function validateColumns()
    {
        $this->validateDubpicateColumns();
        $this->validateDubplicatePrimaryKeys();
        $this->validateAutoIncrementColumns();
        $this->validateColumnsLengthParameter();
    }

    protected function validateDubpicateColumns()
    {
        foreach ($this->columns as $outerIndex=>$outerColumn) {
            foreach ($this->columns as $innerIndex=>$innerColumn) {
                if ($innerIndex != $outerIndex && $innerColumn['name'] == $outerColumn['name']) {
                    throw new ValidationException([
                        'columns' => Lang::get('rainlab.builder::lang.database.error_table_duplicate_column', 
                            ['column' => $outerColumn['name']]
                        )
                    ]);
                }
            }
        }
    }

    protected function validateDubplicatePrimaryKeys()
    {
        $keysFound = 0;
        foreach ($this->columns as $column) {
            if ($column['primary_key']) {
                $keysFound++;
            }
        }

        if ($keysFound > 1) {
            throw new ValidationException([
                'columns' => Lang::get('rainlab.builder::lang.database.error_table_mutliple_primary_keys')
            ]);
        }
    }

    protected function validateAutoIncrementColumns()
    {
        $autoIncrement = null;
        foreach ($this->columns as $column) {
            if (!$column['auto_increment']) {
                continue;
            }

            if ($autoIncrement) {
                throw new ValidationException([
                    'columns' => Lang::get('rainlab.builder::lang.database.error_table_mutliple_auto_increment')
                ]);
            }

            $autoIncrement = $column;
        }

        if (!$autoIncrement) {
            return;
        }

        if (!in_array($autoIncrement['type'], MigrationColumnType::getIntegerTypes())) {
            throw new ValidationException([
                'columns' => Lang::get('rainlab.builder::lang.database.error_table_auto_increment_non_integer')
            ]);
        }
    }

    protected function validateColumnsLengthParameter()
    {
        foreach ($this->columns as $column) {
            try {
                MigrationColumnType::validateLength($column['type'], $column['length']);
            }
            catch (Exception $ex) {
                throw new ValidationException([
                    'columns' => $ex->getMessage()
                ]);
            }
        }
    }

    protected static function getSchemaManager()
    {
        if (!self::$schemaManager) {
            self::$schemaManager = Schema::getConnection()->getDoctrineSchemaManager();
        }

        return self::$schemaManager;
    }

    protected static function getSchema()
    {
        if (!self::$schema) {
            self::$schema = self::getSchemaManager()->createSchema();
        }

        return self::$schema;
    }
}