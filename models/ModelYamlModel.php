<?php namespace RainLab\Builder\Models;

use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Classes\PluginCode;
use DirectoryIterator;
use SystemException;
use Exception;
use Lang;
use File;
use Yaml;

/**
 * ModelYamlModel is a base class for models belonging to database models (forms, lists, etc.).
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class ModelYamlModel extends YamlModel
{
    /**
     * @var string fileName
     */
    public $fileName;

    /**
     * @var string modelClassName
     */
    protected $modelClassName;

    /**
     * fill
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (strlen($this->fileName)) {
            $this->fileName = $this->addExtension($this->fileName);
        }
    }

    /**
     * setModelClassName
     */
    public function setModelClassName($className)
    {
        if (!preg_match('/^[a-zA-Z]+[0-9a-z\_]*$/', $className)) {
            throw new SystemException('Invalid class name: '.$className);
        }

        $this->modelClassName = $className;
    }

    /**
     * validate
     */
    public function validate()
    {
        $this->validationMessages = [
            'fileName.required' => Lang::get('rainlab.builder::lang.form.error_file_name_required'),
            'fileName.regex' => Lang::get('rainlab.builder::lang.form.error_file_name_invalid')
        ];

        return parent::validate();
    }

    /**
     * getDisplayName returns a string suitable for displaying in the Builder UI tabs.
     */
    public function getDisplayName($nameFallback)
    {
        $fileName = $this->fileName;

        if (substr($fileName, -5) == '.yaml') {
            $fileName = substr($fileName, 0, -5);
        }

        if (!strlen($fileName)) {
            $fileName = $nameFallback;
        }

        return $this->getModelClassName().'/'.$fileName;
    }

    /**
     * listModelFiles
     */
    public static function listModelFiles($pluginCodeObj, $modelClassName)
    {
        if (!self::validateModelClassName($modelClassName)) {
            throw new SystemException('Invalid model class name: '.$modelClassName);
        }

        $modelDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/models/'.strtolower($modelClassName);

        $modelDirectoryPath = File::symbolizePath($modelDirectoryPath);

        if (!File::isDirectory($modelDirectoryPath)) {
            return [];
        }

        $result = [];
        foreach (new DirectoryIterator($modelDirectoryPath) as $fileInfo) {
            if (!$fileInfo->isFile() || $fileInfo->getExtension() != 'yaml') {
                continue;
            }

            try {
                $fileContents = Yaml::parseFile($fileInfo->getPathname());
            }
            catch (Exception $ex) {
                continue;
            }

            if (!is_array($fileContents)) {
                $fileContents = [];
            }

            if (!static::validateFileIsModelType($fileContents)) {
                continue;
            }

            $result[] = $fileInfo->getBasename();
        }

        return $result;
    }

    /**
     * getPluginRegistryData
     */
    public static function getPluginRegistryData($pluginCode, $modelClassName)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $classParts = explode('\\', $modelClassName);
        if (!$classParts) {
            return [];
        }

        $modelClassName = array_pop($classParts);

        if (!self::validateModelClassName($modelClassName)) {
            return [];
        }

        $models = self::listModelFiles($pluginCodeObj, $modelClassName);
        $modelDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/models/'.strtolower($modelClassName).'/';

        $result = [];
        foreach ($models as $fileName) {
            $fullFilePath = $modelDirectoryPath.$fileName;

            $result[$fullFilePath] = $fileName;
        }

        return $result;
    }

    /**
     * getPluginRegistryDataAllRecords
     */
    public static function getPluginRegistryDataAllRecords($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);
        $pluginDirectoryPath = $pluginCodeObj->toPluginDirectoryPath();

        $models = ModelModel::listPluginModels($pluginCodeObj);
        $result = [];
        foreach ($models as $model) {
            $modelRecords = self::listModelFiles($pluginCodeObj, $model->className);
            $modelDirectoryPath = $pluginDirectoryPath.'/models/'.strtolower($model->className).'/';

            foreach ($modelRecords as $fileName) {
                $label = $model->className.'/'.$fileName;
                $key = $modelDirectoryPath.$fileName;

                $result[$key] = $label;
            }
        }

        return $result;
    }

    /**
     * validateFileIsModelType
     */
    public static function validateFileIsModelType($fileContentsArray)
    {
        return false;
    }

    /**
     * validateModelClassName
     */
    protected static function validateModelClassName($modelClassName)
    {
        return preg_match('/^[A-Z]+[a-zA-Z0-9_]+$/i', $modelClassName);
    }

    /**
     * getModelClassName
     */
    protected function getModelClassName()
    {
        if ($this->modelClassName === null) {
            throw new SystemException('The model class name is not set.');
        }

        return $this->modelClassName;
    }

    /**
     * getYamlFilePath
     */
    public function getYamlFilePath()
    {
        $fileName = $this->addExtension(trim($this->fileName));

        return File::symbolizePath($this->getPluginCodeObj()->toPluginDirectoryPath().'/models/'.strtolower($this->getModelClassName()).'/'.$fileName);
    }

    /**
     * getFilePath returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        $fileName = trim($this->fileName);
        if (!strlen($fileName)) {
            throw new SystemException('The form model file name is not set.');
        }

        $fileName = $this->addExtension($fileName);

        return $this->getPluginCodeObj()->toPluginDirectoryPath().'/models/'.strtolower($this->getModelClassName()).'/'.$fileName;
    }

    /**
     * addExtension
     */
    protected function addExtension($fileName)
    {
        if (substr($fileName, -5) !== '.yaml') {
            $fileName .= '.yaml';
        }

        return $fileName;
    }
}
