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
    public $className;

    public $databaseTable;

    protected static $fillable = [
        'className',
        'databaseTable'
    ];

    protected $validationRules = [
        'className' => ['required', 'regex:/^[A-Z]+[a-zA-Z0-9_]+$/', 'uniqModelName'],
        'databaseTable' => ['required']
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

        $generator = new FilesystemGenerator('$', $structure, '$/rainlab/builder/classes/modelmodel/templates');
        $generator->setVariables($variables);
        $generator->generate();
    }

    public function validate()
    {
        $path = File::symbolizePath('$/'.$this->getFilePath());

        $this->validationMessages = [
            'className.uniq_model_name' => Lang::get('rainlab.builder::lang.model.error_class_name_exists', ['path'=>$path])
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

        parent::validate();
    }

    public function getDatabaseTableOptions()
    {
        $pluginCode = $this->getPluginCodeObj()->toCode();

        $tables = DatabaseTableModel::listPluginTables($pluginCode);
        return array_combine($tables, $tables);
    }

    public static function getModelFields($pluginCodeObj, $modelClassName)
    {
        if (!self::validateModelClassName($modelClassName)) {
            throw new SystemException('Invalid model class name: '.$modelClassName);
        }

        $modelsDirectoryPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/models');
        if (!File::isDirectory($modelsDirectoryPath)) {
            return [];
        }

        $modelFilePath = $modelsDirectoryPath.'/'.$modelClassName.'.php';
        if (!File::isFile($modelFilePath)) {
            return [];
        }

        $parser = new ModelFileParser();
        $modelInfo = $parser->extractModelInfoFromSource(File::get($modelFilePath));
        if (!$modelInfo || !isset($modelInfo['table'])) {
            return [];
        }

        $tableName = $modelInfo['table'];

        // Currently we return only table columns,
        // but eventually we might want to return relations as well.

        return Schema::getColumnListing($tableName);
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

    public static function validateModelClassName($modelClassName, $allowNamespaces = false)
    {
        if (!$allowNamespaces) {
            return preg_match('/^[A-Z]+[a-zA-Z0-9_]+$/', $modelClassName);
        }

        return preg_match('/^[A-Z]+[a-zA-Z0-9_\\\\]+$/', $modelClassName);
    }

    protected function getFilePath()
    {
        return $this->getPluginCodeObj()->toFilesystemPath().'/models/'.$this->className.'.php';
    }
}