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

    public $baseModelClassName;

    protected static $fillable = [
        'controller',
        'behaviors',
        'baseModelClassName'
    ];

    protected $validationRules = [
        'controller' => ['regex:/^[a-zA-Z]+[a-zA-Z0-9_]+$/']
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
        $this->validate();

        $controllerPath = $this->getControllerFilePath();
        if (!File::isFile($controllerPath)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_controller_not_found'));
        }

        if (!is_array($this->behaviors)) {
            throw new SystemException('The behaviors data should be an array.');
        }

        $fileContents = File::get($controllerPath);

        $parser = new ControllerFileParser($fileContents);

        $behaviors = $parser->listBehaviors();
        if (!$behaviors) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_controller_has_no_behaviors'));
        }

        $library = ControllerBehaviorLibrary::instance();
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

            if (array_key_exists($behaviorClass, $this->behaviors)) {
                $this->saveBehaviorConfiguration($propertyValue, $this->behaviors[$behaviorClass], $behaviorClass);
            }
        }
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (is_array($this->behaviors)) {
            foreach ($this->behaviors as $class=>&$configuration) {
                if (is_scalar($configuration)) {
                    $configuration = json_decode($configuration, true);
                }
            }
        }
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
        if (!File::isFile($filePath)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_controller_not_found'));
        }

        $fileContents = File::get($filePath);

        $parser = new ControllerFileParser($fileContents);

        $behaviors = $parser->listBehaviors();
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

            $configuration = $this->loadBehaviorConfiguration($propertyValue, $behaviorClass);
            if ($configuration === false) {
                continue;
            }

            $this->behaviors[$behaviorClass] = $configuration;
        }
    }

    protected function loadBehaviorConfiguration($fileName, $behaviorClass)
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

    protected function saveBehaviorConfiguration($fileName, $configuration, $behaviorClass)
    {
        if (!preg_match('/^[a-z0-9\.\-_]+$/i', $fileName)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_invalid_config_file_name', ['file'=>$fileName, 'class'=>$behaviorClass]));
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (strlen($extension) && $extension != 'yaml') {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_file_not_yaml', ['file'=>$fileName, 'class'=>$behaviorClass]));
        }

        $controllerPath = $this->getControllerFilePath(true);
        $filePath = $controllerPath.'/'.$fileName;

        $fileDirectory = dirname($filePath);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_make_dir', ['name'=>$fileDirectory]));
            }
        }

        $dumper = new YamlDumper();
        if ($configuration !== null) {
            $yamlData = $dumper->dump($configuration, 20, 0, false, true);
        }
        else {
            $yamlData = '';
        }

        if (@File::put($filePath, $yamlData) === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.yaml.save_error', ['name'=>$filePath]));
        }

        @File::chmod($filePath);
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