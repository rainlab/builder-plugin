<?php namespace RainLab\Builder\Classes;

use DirectoryIterator;
use SystemException;
use Validator;
use Lang;
use File;
use Schema;
use Str;
use Db;

/**
 * Manages plugin models.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelModel extends BaseModel
{
    const UNQUALIFIED_CLASS_NAME_PATTERN = '/^[A-Z]+[a-zA-Z0-9_]+$/';

    public $className;

    public $databaseTable;

    protected static $fillable = [
        'className',
        'databaseTable',
        'addTimestamps',
        'addSoftDeleting'
    ];

    protected $validationRules = [
        'className' => ['required', 'regex:' . self::UNQUALIFIED_CLASS_NAME_PATTERN, 'uniqModelName'],
        'databaseTable' => ['required'],
        'addTimestamps' => ['timestampColumnsMustExist'],
        'addSoftDeleting' => ['deletedAtColumnMustExist']
    ];

    public static function listPluginModels($pluginCodeObj)
    {
        $modelsDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/models';
        $pluginNamespace = $pluginCodeObj->toPluginNamespace();

        $modelsDirectoryPath = File::symbolizePath($modelsDirectoryPath);
        if (!File::isDirectory($modelsDirectoryPath)) {
            return [];
        }

        $parser = new ModelFileParser();
        $result = [];
        foreach (new DirectoryIterator($modelsDirectoryPath) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if ($fileInfo->getExtension() != 'php') {
                continue;
            }

            $filePath = $fileInfo->getPathname();
            $contents = File::get($filePath);

            $modelInfo = $parser->extractModelInfoFromSource($contents);
            if (!$modelInfo) {
                continue;
            }

            if (!Str::startsWith($modelInfo['namespace'], $pluginNamespace.'\\')) {
                continue;
            }

            $model = new ModelModel();
            $model->className = $modelInfo['class'];
            $model->databaseTable = isset($modelInfo['table']) ? $modelInfo['table'] : null;

            $result[] = $model;
        }

        return $result;
    }

    public function save()
    {
        $this->validate();

        $modelFilePath = $this->getFilePath();
        $namespace = $this->getPluginCodeObj()->toPluginNamespace().'\\Models';

        $structure = [
            $modelFilePath => 'model.php.tpl'
        ];

        $variables = [
            'namespace' => $namespace,
            'classname' => $this->className,
            'table' => $this->databaseTable
        ];

        $dynamicContents = [];

        $generator = new FilesystemGenerator('$', $structure, '$/rainlab/builder/classes/modelmodel/templates');
        $generator->setVariables($variables);

        if ($this->addSoftDeleting) {
            $dynamicContents[] = $generator->getTemplateContents('soft-delete.php.tpl');
        }

        if (!$this->addTimestamps) {
            $dynamicContents[] = $generator->getTemplateContents('no-timestamps.php.tpl');
        }

        $generator->setVariable('dynamicContents', implode('', $dynamicContents));

        $generator->generate();
    }

    public function validate()
    {
        $path = File::symbolizePath('$/'.$this->getFilePath());

        $this->validationMessages = [
            'className.uniq_model_name' => Lang::get('rainlab.builder::lang.model.error_class_name_exists', ['path'=>$path]),
            'addTimestamps.timestamp_columns_must_exist' => Lang::get('rainlab.builder::lang.model.error_timestamp_columns_must_exist'),
            'addSoftDeleting.deleted_at_column_must_exist' => Lang::get('rainlab.builder::lang.model.error_deleted_at_column_must_exist')
        ];

        Validator::extend('uniqModelName', function($attribute, $value, $parameters) use ($path) {
            $value = trim($value);

            if (!$this->isNewModel()) {
                // Editing models is not supported at the moment, 
                // so no validation is required.
                return true;
            }

            return !File::isFile($path);
        });

        $columns = $this->isNewModel() ? Schema::getColumnListing($this->databaseTable) : [];
        Validator::extend('timestampColumnsMustExist', function($attribute, $value, $parameters) use ($columns) {
            return $this->validateColumnsExist($value, $columns, ['created_at', 'updated_at']);
        });

        Validator::extend('deletedAtColumnMustExist', function($attribute, $value, $parameters) use ($columns) {
            return $this->validateColumnsExist($value, $columns, ['deleted_at']);
        });

        parent::validate();
    }

    public function getDatabaseTableOptions()
    {
        $pluginCode = $this->getPluginCodeObj()->toCode();

        $tables = DatabaseTableModel::listPluginTables($pluginCode);
        return array_combine($tables, $tables);
    }

    private static function getTableNameFromModelClass($pluginCodeObj, $modelClassName)
    {
        if (!self::validateModelClassName($modelClassName)) {
            throw new SystemException('Invalid model class name: '.$modelClassName);
        }

        $modelsDirectoryPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/models');
        if (!File::isDirectory($modelsDirectoryPath)) {
            return '';
        }

        $modelFilePath = $modelsDirectoryPath.'/'.$modelClassName.'.php';
        if (!File::isFile($modelFilePath)) {
            return '';
        }

        $parser = new ModelFileParser();
        $modelInfo = $parser->extractModelInfoFromSource(File::get($modelFilePath));
        if (!$modelInfo || !isset($modelInfo['table'])) {
            return '';
        }

        return $modelInfo['table'];
    }

    public static function getModelFields($pluginCodeObj, $modelClassName)
    {
        $tableName = self::getTableNameFromModelClass($pluginCodeObj, $modelClassName);

        // Currently we return only table columns,
        // but eventually we might want to return relations as well.

        return Schema::getColumnListing($tableName);
    }

    public static function getModelColumnsAndTypes($pluginCodeObj, $modelClassName)
    {
        $tableName = self::getTableNameFromModelClass($pluginCodeObj, $modelClassName);

        if (!DatabaseTableModel::tableExists($tableName)) {
            throw new ApplicationException('Database table not found: '.$tableName);
        }

        $schema = DatabaseTableModel::getSchema();
        $tableInfo = $schema->getTable($tableName);

        $columns = $tableInfo->getColumns();
        $result = [];
        foreach ($columns as $column) {
            $columnName = $column->getName();
            $typeName = $column->getType()->getName();

            if ($typeName == EnumDbType::TYPENAME) {
                continue;
            }

            $item = [
                'name' => $columnName,
                'type' => MigrationColumnType::toMigrationMethodName($typeName, $columnName)
            ];

            $result[] = $item;
        }

        return $result;
    }

    public static function getPluginRegistryData($pluginCode, $subtype)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $models = self::listPluginModels($pluginCodeObj);
        $result = [];
        foreach ($models as $model) {
            $fullClassName = $pluginCodeObj->toPluginNamespace().'\\Models\\'.$model->className;

            $result[$fullClassName] = $model->className;
        }

        return $result;
    }

    public static function getPluginRegistryDataColumns($pluginCode, $modelClassName)
    {
        $classParts = explode('\\', $modelClassName);
        if (!$classParts) {
            return [];
        }

        $modelClassName = array_pop($classParts);

        if (!self::validateModelClassName($modelClassName)) {
            return [];
        }

        $pluginCodeObj = new PluginCode($pluginCode);
        $columnNames = self::getModelFields($pluginCodeObj, $modelClassName);
        
        $result = [];
        foreach ($columnNames as $columnName) {
            $result[$columnName] = $columnName;
        }

        return $result;
    }

    public static function validateModelClassName($modelClassName)
    {
      return class_exists($modelClassName) || !!preg_match(self::UNQUALIFIED_CLASS_NAME_PATTERN, $modelClassName);
    }

    protected function getFilePath()
    {
        return $this->getPluginCodeObj()->toFilesystemPath().'/models/'.$this->className.'.php';
    }

    protected function validateColumnsExist($value, $columns, $columnsToCheck)
    {
        if (!strlen(trim($this->databaseTable))) {
            return true;
        }

        if (!$this->isNewModel()) {
            // Editing models is not supported at the moment, 
            // so no validation is required.
            return true;
        }

        if (!$value) {
            return true;
        }

        return count(array_intersect($columnsToCheck, $columns)) == count($columnsToCheck);
    }
}