<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use SystemException;
use DirectoryIterator;
use ValidationException;
use Yaml;
use Exception;
use Lang;
use File;

/**
 * Represents and manages plugin controllers.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerModel extends BaseModel
{
    public $controller;

    public $behaviors = [];

    protected static $fillable = [
    ];

    protected $validationRules = [
    ];

    public function load($controller)
    {
        if (!$this->validateFileName($controller)) {
            throw new SystemException('Invalid controller file name: '.$language);
        }
        
        $this->controller = $this->trimExtension($controller);
        $this->loadControllerBehaviors();
    }

    public function save()
    {
    }

    public static function listPluginControllers($pluginCodeObj)
    {
        $controllersDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/controllers';

        $controllersDirectoryPath = File::symbolizePath($controllersDirectoryPath);

        if (!File::isDirectory($controllersDirectoryPath)) {
            return [];
        }

        $result = [];
        foreach (new DirectoryIterator($controllersDirectoryPath) as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }

            if ($fileInfo->getExtension() !== 'php') {
                continue;
            }

            $result[] =  $fileInfo->getBasename('.php');
        }

        return $result;
    }

    protected function loadControllerBehaviors()
    {
        $filePath = $this->getControllerFilePath();
        $fileContents = File::get($filePath);

        $parser = new ControllerFileParser($fileContents);

        $behaviors = $parser->listBehaviors('listConfig');
        if (!$behaviors) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_controller_has_no_behaviors'));
        }

        $library = ControllerBehaviorLibrary::instance();
        $this->behaviors = [];
        foreach ($behaviors as $behaviorClass) {
            $behaviorInfo = $library->getBehaviorInfo($behaviorClass);

            if (!$behaviorInfo) {
                continue;
            }

            $propertyName = $behaviorInfo['configPropertyName'];
            $propertyValue = $parser->getStringPropertyValue($propertyName);
            if (!strlen($propertyValue)) {
                continue;
            }

            $configuration = $this->loadBehaviorConfiguration($propertyValue);
            if ($configuration === false) {
                continue;
            }

            $this->behaviors[$behaviorClass] = $configuration;
        }
    }

    protected function loadBehaviorConfiguration($fileName)
    {
        if (!preg_match('/^[a-z0-9\.\-_]+$/i', $fileName)) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (strlen($extension) && $extension != 'yaml') {
            return false;
        }

        $controllerPath = $this->getControllerFilePath(true);
        $filePath = $controllerPath.'/'.$fileName;

        if (!File::isFile($filePath)) {
            return false;
        }

        try {
            return Yaml::parse(File::get($filePath));
        } 
        catch (Exception $ex) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_invalid_yaml_configuration', ['file'=>$fileName]));
        }
    }

    protected function getControllerFilePath($controllerFilesDirectory = false)
    {
        $pluginCodeObj = $this->getPluginCodeObj();
        $controllersDirectoryPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/controllers');

        if (!$controllerFilesDirectory) {
            return $controllersDirectoryPath.'/'.$this->controller.'.php';
        }

        return $controllersDirectoryPath.'/'.strtolower($this->controller);
    }

    protected function trimExtension($fileName)
    {
        if (substr($fileName, -4) == '.php') {
            return substr($fileName, 0, -4);
        }

        return $fileName;
    }

    protected function validateFileName($fileName)
    {
        if (!preg_match('/^[a-z0-9\.\-_]+$/i', $fileName)) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (strlen($extension) && $extension != 'php') {
            return false;
        }

        return true;
    }
}