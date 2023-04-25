<?php namespace RainLab\Builder\Models;

use Str;
use Twig;
use Lang;
use File;
use Schema;
use Validator;
use RainLab\Builder\Classes\EnumDbType;
use RainLab\Builder\Classes\FilesystemGenerator;
use RainLab\Builder\Classes\MigrationColumnType;
use RainLab\Builder\Classes\ModelFileParser;
use RainLab\Builder\Classes\PluginCode;
use DirectoryIterator;
use ApplicationException;
use SystemException;

/**
 * ModelModel manages plugin models.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelModel extends BaseModel
{
    const UNQUALIFIED_CLASS_NAME_PATTERN = '/^[A-Z]+[a-zA-Z0-9_]+$/';

    /**
     * @var string className
     */
    public $className;

    /**
     * @var string baseClassName
     */
    public $baseClassName = \Model::class;

    /**
     * @var string databaseTable
     */
    public $databaseTable;

    /**
     * @var array traits
     */
    public $traits = [
        \October\Rain\Database\Traits\Validation:: class
    ];

    /**
     * @var array relationDefinitions (belongsTo, belongsToMany, etc.)
     */
    public $relationDefinitions;

    /**
     * @var array validationDefinitions (rules, attributeNames, customMessages)
     */
    public $validationDefinitions;

    /**
     * @var array multisiteDefinition (fields, sync)
     */
    public $multisiteDefinition;

    /**
     * @var bool addSoftDeleting
     */
    public $addTimestamps = false;

    /**
     * @var bool addSoftDeleting
     */
    public $addSoftDeleting = false;

    /**
     * @var bool skipDbValidation
     */
    public $skipDbValidation = false;

    /**
     * @var array injectedRawContents
     */
    protected $injectedRawContents = [];

    /**
     * @var array fillable
     */
    protected static $fillable = [
        'className',
        'baseClassName',
        'databaseTable',
        'relationDefinitions',
        'addTimestamps',
        'addSoftDeleting',
    ];

    /**
     * @var array validationRules
     */
    protected $validationRules = [
        'className' => ['required', 'regex:' . self::UNQUALIFIED_CLASS_NAME_PATTERN, 'uniqModelName'],
        'databaseTable' => ['required'],
        'addTimestamps' => ['timestampColumnsMustExist'],
        'addSoftDeleting' => ['deletedAtColumnMustExist']
    ];

    /**
     * listPluginModels
     */
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

    /**
     * save
     */
    public function save()
    {
        $this->validate();

        $modelFilePath = $this->getFilePath();
        $namespace = $this->getPluginCodeObj()->toPluginNamespace().'\\Models';
        $templateFile = $this->baseClassName === \System\Models\SettingModel::class
            ? 'settingmodel.php.tpl'
            : 'model.php.tpl';

        $structure = [
            $modelFilePath => $templateFile
        ];

        $variables = [
            'namespace' => $namespace,
            'classname' => $this->className,
            'baseclass' => $this->baseClassName,
            'baseclassname' => class_basename($this->baseClassName),
            'table' => $this->databaseTable
        ];

        $generator = new FilesystemGenerator('$', $structure, '$/rainlab/builder/models/modelmodel/templates');
        $generator->setVariables($variables);

        // Trait contents
        if ($this->addSoftDeleting) {
            $this->traits[] = \October\Rain\Database\Traits\SoftDelete::class;
        }

        usort($this->traits, function($a, $b) { return strlen($a) > strlen($b); });

        $traitContents = [];
        foreach ($this->traits as $trait) {
            $traitContents[] = "    use \\{$trait};";
        }
        $generator->setVariable('traitContents', implode(PHP_EOL, $traitContents));

        // Dynamic contents
        $dynamicContents = [];

        if ($this->addSoftDeleting) {
            $dynamicContents[] = $generator->getTemplateContents('soft-delete.php.tpl');
        }

        if (!$this->addTimestamps) {
            $dynamicContents[] = $generator->getTemplateContents('no-timestamps.php.tpl');
        }

        $dynamicContents = array_merge($dynamicContents, (array) $this->injectedRawContents);

        $generator->setVariable('dynamicContents', implode('', $dynamicContents));

        // Validation contents
        $validationDefinitions = (array) $this->validationDefinitions;
        foreach ($validationDefinitions as $type => &$definitions) {
            foreach ($definitions as $field => &$rule) {
                // Cannot process anything other than string at this time
                if (!is_string($rule)) {
                    unset($definitions[$field]);
                }
            }
        }

        $validationTemplate = File::get(__DIR__.'/modelmodel/templates/validation-definitions.php.tpl');

        $validationContents = Twig::parse($validationTemplate, ['validation' => $validationDefinitions]);

        $generator->setVariable('validationContents', $validationContents);

        // Relation contents
        $relationContents = [];

        $relationTemplate = File::get(__DIR__.'/modelmodel/templates/relation-definitions.php.tpl');

        foreach ((array) $this->relationDefinitions as $relationType => $definitions) {
            if (!$definitions) {
                continue;
            }

            $relationVars = [
                'relationType' => $relationType,
                'relations' => [],
            ];

            foreach ($definitions as $relationName => $definition) {
                $definition = (array) $definition;
                $modelClass = array_shift($definition);

                $props = $definition;
                foreach ($props as &$prop) {
                    $prop = var_export($prop, true);
                }

                $relationVars['relations'][$relationName] = [
                    'class' => $modelClass,
                    'props' => $props
                ];
            }

            $relationContents[] = Twig::parse($relationTemplate, $relationVars);
        }

        $generator->setVariable('relationContents', implode(PHP_EOL, $relationContents));

        // Multisite contents
        $multisiteTemplate = File::get(__DIR__.'/modelmodel/templates/multisite-definitions.php.tpl');

        $multisiteContents = Twig::parse($multisiteTemplate, ['multisite' => $this->multisiteDefinition]);

        $generator->setVariable('multisiteContents', $multisiteContents);

        $generator->generate();
    }

    /**
     * validate
     */
    public function validate()
    {
        $path = File::symbolizePath('$/'.$this->getFilePath());

        $this->validationMessages = [
            'className.uniq_model_name' => Lang::get('rainlab.builder::lang.model.error_class_name_exists', ['path'=>$path]),
            'addTimestamps.timestamp_columns_must_exist' => Lang::get('rainlab.builder::lang.model.error_timestamp_columns_must_exist'),
            'addSoftDeleting.deleted_at_column_must_exist' => Lang::get('rainlab.builder::lang.model.error_deleted_at_column_must_exist')
        ];

        Validator::extend('uniqModelName', function ($attribute, $value, $parameters) use ($path) {
            $value = trim($value);

            if (!$this->isNewModel()) {
                // Editing models is not supported at the moment,
                // so no validation is required.
                return true;
            }

            return !File::isFile($path);
        });

        $columns = $this->isNewModel() ? Schema::getColumnListing($this->databaseTable) : [];
        Validator::extend('timestampColumnsMustExist', function ($attribute, $value, $parameters) use ($columns) {
            return $this->validateColumnsExist($value, $columns, ['created_at', 'updated_at']);
        });

        Validator::extend('deletedAtColumnMustExist', function ($attribute, $value, $parameters) use ($columns) {
            return $this->validateColumnsExist($value, $columns, ['deleted_at']);
        });

        if ($this->skipDbValidation) {
            unset(
                $this->validationRules['addTimestamps'],
                $this->validationRules['addSoftDeleting']
            );
        }

        parent::validate();
    }

    /**
     * addRawContentToModel
     */
    public function addRawContentToModel($content)
    {
        $this->injectedRawContents[] = $content;
    }

    /**
     * getDatabaseTableOptions
     */
    public function getDatabaseTableOptions()
    {
        $pluginCode = $this->getPluginCodeObj()->toCode();

        $tables = DatabaseTableModel::listPluginTables($pluginCode);
        return array_combine($tables, $tables);
    }

    /**
     * getTableNameFromModelClass
     */
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

    /**
     * getModelFields
     */
    public static function getModelFields($pluginCodeObj, $modelClassName)
    {
        $tableName = self::getTableNameFromModelClass($pluginCodeObj, $modelClassName);

        // Currently we return only table columns,
        // but eventually we might want to return relations as well.

        return Schema::getColumnListing($tableName);
    }

    /**
     * getModelColumnsAndTypes
     */
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

    /**
     * getPluginRegistryData
     */
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

    /**
     * getPluginRegistryDataColumns
     */
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

    /**
     * validateModelClassName
     */
    public static function validateModelClassName($modelClassName)
    {
        return class_exists($modelClassName) || !!preg_match(self::UNQUALIFIED_CLASS_NAME_PATTERN, $modelClassName);
    }

    /**
     * getModelFilePath
     */
    public function getModelFilePath()
    {
        return File::symbolizePath($this->getPluginCodeObj()->toPluginDirectoryPath().'/models/'.$this->className.'.php');
    }

    /**
     * getFilePath
     */
    protected function getFilePath()
    {
        return $this->getPluginCodeObj()->toFilesystemPath().'/models/'.$this->className.'.php';
    }

    /**
     * validateColumnsExist
     */
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
