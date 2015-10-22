<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use Exception;
use Lang;
use File;

/**
 * A base class for models belonging to databse models (forms, lists, etc.).
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class ModelYamlModel extends YamlModel
{
    public $fileName;

    protected $modelClassName;

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (strlen($this->fileName)) {
            $this->fileName = $this->addExtension($this->fileName);
        }
    }

    public function setModelClassName($className)
    {
        if (!preg_match('/^[a-zA-Z]+[0-9a-z\_]*$/', $className)) {
            throw new SystemException('Invalid class name: '.$className);
        }

        $this->modelClassName = $className;
    }

    public function validate()
    {
        $this->validationMessages = [
            'fileName.required' => Lang::get('rainlab.builder::lang.form.error_file_name_required'),
            'fileName.regex' => Lang::get('rainlab.builder::lang.form.error_file_name_invalid')
        ];

        return parent::validate();
    }

    /**
     * Returns a string suitable for displaying in the Builder UI tabs.
     */
    public function getDisplayName()
    {
        $fileName = $this->fileName;

        if (substr($fileName, -5) == '.yaml') {
            $fileName = substr($fileName, 0, -5);
        }

        return $this->getModelClassName().'/'.$fileName;
    }

    public static function listModelFiles($pluginCodeObj, $modelClassName)
    {

    }

    protected function getModelClassName()
    {
        if ($this->modelClassName === null) {
            throw new SystemException('The model class name is not set.');
        }

        return $this->modelClassName;
    }


    /**
     * Returns a file path to save the model to.
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

    protected function addExtension($fileName) {
        if (substr($fileName, -5) !== '.yaml') {
            $fileName .= '.yaml';
        }

        return $fileName;
    }
}