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
traceLog($this->behaviors);
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
traceLog($behaviorClass);
            $behaviorInfo = $library->getBehaviorInfo($behaviorClass);

            if (!$behaviorInfo) {
traceLog('Skip 1');
                continue;
            }

            $propertyName = $behaviorInfo['configPropertyName'];
            $propertyValue = $parser->getStringPropertyValue($propertyName);
            if (!strlen($propertyValue)) {
traceLog('Skip 2');
                continue;
            }

            $configuration = $this->loadBehaviorConfiguration($propertyValue);
            if ($configuration === false) {
traceLog('Skip 3');
                continue;
            }

            $this->behaviors[$behaviorClass] = $configuration;
        }
    }

    protected function loadBehaviorConfiguration($fileName)
    {
        if (!preg_match('/^[a-z0-9\.]+$/i', $fileName)) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (strlen($extension) && $extension != 'yaml') {
            return false;
        }

        $controllerPath = $this->getControllerFilePath();
        $filePath = $controllerPath.'/'.strtolower($this->controller).'/'.$fileName;
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

    protected function getControllerFilePath()
    {
        $pluginCodeObj = $this->getPluginCodeObj();
        $controllersDirectoryPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/controllers');

        return $controllersDirectoryPath.'/'.$this->controller.'.php';
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
        if (!preg_match('/^[a-z0-9\.]+$/i', $fileName)) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (strlen($extension) && $extension != 'php') {
            return false;
        }

        return true;
    }
}