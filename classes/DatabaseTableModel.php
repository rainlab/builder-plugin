<?php namespace RainLab\Builder\Classes;

use Doctrine\DBAL\Types\Type;
use ApplicationException;
use ValidationException;
use SystemException;
use Exception;
use Validator;
use Lang;
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
        'name' => ['required', 'regex:/^[a-z]+[a-z0-9_]+$/', 'tablePrefix', 'uniqueTableName', 'max:64']
    ];

    /**
     * @var \Doctrine\DBAL\Schema\Table Table details loaded from the database.
     */
    protected $tableInfo;

    /**
     * @var \Doctrine\DBAL\Schema\AbstractSchemaManager Contains the database schema
     */
    protected static $schemaManager = null;

    /**
     * @var \Doctrine\DBAL\Schema\Schema Contains the database schema
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
        $this->loadColumnsFromTableInfo();
        $this->exists = true;
    }

    public function validate()
    {
        $pluginDbPrefix = $this->getPluginCodeObj()->toDatabasePrefix();

        if (!strlen($pluginDbPrefix)) {
            throw new SystemException('Error saving the table model - the plugin database prefix is not set for the object.');
        }

        $prefix = $pluginDbPrefix.'_';

        $this->validationMessages = [
            'name.table_prefix' => Lang::get('rainlab.builder::lang.database.error_table_name_invalid_prefix', [
                'prefix' => $prefix
            ]),
            'name.regex' => Lang::get('rainlab.builder::lang.database.error_table_name_invalid_characters'),
            'name.unique_table_name' => Lang::get('rainlab.builder::lang.database.error_table_already_exists', ['name'=>$this->name]),
            'name.max' => Lang::get('rainlab.builder::lang.database.error_table_name_too_long')
        ];

        Validator::extend('tablePrefix', function($attribute, $value, $parameters) use ($prefix) {
            $value = trim($value);

            if (!Str::startsWith($value, $prefix)) {
                return false;
            }

            return true;
        });

        Validator::extend('uniqueTableName', function($attribute, $value, $parameters) {
            $value = trim($value);

            $schema = $this->getSchema();
            if ($this->isNewModel()) {
                return !$schema->hasTable($value);
            }

            if ($value != $this->tableInfo->getName()) {
                return !$schema->hasTable($value);
            }

            return true;
        });

        $this->validateColumns();

        return parent::validate();
    }

    public function generateCreateOrUpdateMigration()
    {
        $schemaCreator = new DatabaseTableSchemaCreator();
        $existingSchema = $this->tableInfo;
        $newTableName = $this->name;
        $tableName = $existingSchema ? $existingSchema->getName() : $this->name;

        $newSchema = $schemaCreator->createTableSchema($tableName, $this->columns);

        $codeGenerator = new TableMigrationCodeGenerator();
        $migrationCode = $codeGenerator->createOrUpdateTable($newSchema, $existingSchema, $newTableName);
        if ($migrationCode === false) {
            return $migrationCode;
        }

        $description = $existingSchema ? 'Updated table %s' : 'Created table %s';
        return $this->createMigrationObject($migrationCode, sprintf($description, $tableName));
    }

    public function generateDropMigration()
    {
        $existingSchema = $this->tableInfo;
        $codeGenerator = new TableMigrationCodeGenerator();
        $migrationCode = $codeGenerator->dropTable($existingSchema);

        return $this->createMigrationObject($migrationCode, sprintf('Drop table %s', $this->name));
    }

    public static function getSchema()
    {
        if (!self::$schema) {
            self::$schema = self::getSchemaManager()->createSchema();
        }

        return self::$schema;
    }

    protected function validateColumns()
    {
        $this->validateColumnNameLengths();
        $this->validateDupicateColumns();
        $this->validateDubplicatePrimaryKeys();
        $this->validateAutoIncrementColumns();
        $this->validateColumnsLengthParameter();
        $this->validateUnsignedColumns();
        $this->validateDefaultValues();
    }

    protected function validateColumnNameLengths()
    {
        foreach ($this->columns as $column) {
            $name = trim($column['name']);

            if (Str::length($name) > 64) {
                throw new ValidationException([
                    'columns' => Lang::get('rainlab.builder::lang.database.error_column_name_too_long', 
                        ['column' => $name]
                    )
                ]);
            }
        }
    }

    protected function validateDupicateColumns()
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
        $autoIncrementsFound = 0;
        foreach ($this->columns as $column) {
            if ($column['primary_key']) {
                $keysFound++;
            }

            if ($column['auto_increment']) {
                $autoIncrementsFound++;
            }
        }

        if ($keysFound > 1 && $autoIncrementsFound) {
            throw new ValidationException([
                'columns' => Lang::get('rainlab.builder::lang.database.error_table_auto_increment_in_compound_pk')
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

    protected function validateUnsignedColumns()
    {
        foreach ($this->columns as $column) {
            if (!$column['unsigned']) {
                continue;
            }

            if (!in_array($column['type'], MigrationColumnType::getIntegerTypes())) {
                throw new ValidationException([
                    'columns' => Lang::get('rainlab.builder::lang.database.error_unsigned_type_not_int', ['column'=>$column['name']])
                ]);
            }
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

    protected function validateDefaultValues()
    {
        foreach ($this->columns as $column) {
            if (!strlen($column['default'])) {
                continue;
            }

            $default = trim($column['default']);

            if (in_array($column['type'], MigrationColumnType::getIntegerTypes())) {
                if (!preg_match('/^\-?[0-9]+$/', $default)) {
                    throw new ValidationException([
                        'columns' => Lang::get('rainlab.builder::lang.database.error_integer_default_value', ['column'=>$column['name']])
                    ]);
                }

                if ($column['unsigned'] && $default < 0) {
                    throw new ValidationException([
                        'columns' => Lang::get('rainlab.builder::lang.database.error_unsigned_negative_value', ['column'=>$column['name']])
                    ]);
                }

                continue;
            }

            if (in_array($column['type'], MigrationColumnType::getDecimalTypes())) {
                if (!preg_match('/^\-?([0-9]+\.[0-9]+|[0-9]+)$/', $default)) {
                    throw new ValidationException([
                        'columns' => Lang::get('rainlab.builder::lang.database.error_decimal_default_value', ['column'=>$column['name']])
                    ]);
                }

                continue;
            }

            if ($column['type'] == MigrationColumnType::TYPE_BOOLEAN) {
                if (!preg_match('/^0|1$/', $default)) {
                    throw new ValidationException([
                        'columns' => Lang::get('rainlab.builder::lang.database.error_boolean_default_value', ['column'=>$column['name']])
                    ]);
                }
            }
        }
    }

    protected static function getSchemaManager()
    {
        if (!self::$schemaManager) {
            self::$schemaManager = Schema::getConnection()->getDoctrineSchemaManager();

            Type::addType('enumdbtype', 'RainLab\Builder\Classes\EnumDbType');

            // Fixes the problem with enum column type not supported
            // by Doctrine (https://github.com/laravel/framework/issues/1346)
            $platform = self::$schemaManager->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping('enum', 'enumdbtype');
        }

        return self::$schemaManager;
    }

    protected function loadColumnsFromTableInfo()
    {
        $this->columns = [];
        $columns = $this->tableInfo->getColumns();

        $primaryKey = $this->tableInfo->getPrimaryKey();
        $primaryKeyColumns =[];
        if ($primaryKey) {
            $primaryKeyColumns = $primaryKey->getColumns();
        }

        foreach ($columns as $column) {
            $columnName = $column->getName();
            $typeName = $column->getType()->getName();

            if ($typeName == EnumDbType::TYPENAME) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.database.error_enum_not_supported'));
            }

            $item = [
                'name' => $columnName,
                'type' => MigrationColumnType::toMigrationMethodName($typeName, $columnName),
                'length' => MigrationColumnType::doctrineLengthToMigrationLength($column),
                'unsigned' => $column->getUnsigned(),
                'allow_null' => !$column->getNotnull(),
                'auto_increment' => $column->getAutoincrement(),
                'primary_key' => in_array($columnName, $primaryKeyColumns),
                'default' => $column->getDefault(),
                'id' => $columnName,
            ];

            $this->columns[] = $item;
        }
    }

    protected function createMigrationObject($code, $description)
    {
        $migration = new MigrationModel();
        $migration->setPluginCodeObj($this->getPluginCodeObj());

        $migration->code = $code;
        $migration->version = $migration->getNextVersion();
        $migration->description = $description;

        return $migration;
    }
}