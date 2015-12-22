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
use Twig;

/**
 * Helper class for generating controller class files and associated files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerGenerator
{
    protected $sourceModel;

    protected $templateVars;

    protected $configTemplateProperties = [];

    protected $filesGenerated;

    public function __construct($source)
    {
        $this->sourceModel = $source;
    }

    public function generate()
    {
        $this->filesGenerated = [];

        try {
            $this->validateBehaviorViewTemplates();
            $this->validateBehaviorConfigTemplates();
            $this->validateControllerUnique();

            $this->setTemplateVars();
            $this->generateControllerFile();
            $this->generatConfigFiles();
        } catch (Exception $ex) {
            $this->rollback();

            throw $ex;
        }
    }

    protected function validateBehaviorViewTemplates()
    {
        if (!$this->sourceModel->behaviors) {
            return;
        }

        $controllerPath = $this->sourceModel->getControllerFilePath(true);
        $behaviorLibrary = ControllerBehaviorLibrary::instance();

        $knownTemplates = [];
        foreach ($this->sourceModel->behaviors as $behaviorClass) {
            $behaviorInfo = $behaviorLibrary->getBehaviorInfo($behaviorClass);
            if (!$behaviorInfo) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_unknown_behavior', ['class'=>$behaviorClass])
                ]);
            }

            foreach ($behaviorInfo['viewTemplates'] as $viewTemplate) {
                $templateFileName = basename($viewTemplate);
                $templateBaseName = pathinfo($templateFileName, PATHINFO_FILENAME);

                if (in_array($templateFileName, $knownTemplates)) {
                    throw new ValidationException([
                        'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_view_conflict', [
                            'view'=> $templateBaseName
                        ])
                    ]);

                    throw new ApplicationException();
                }

                $knownTemplates[] = $templateFileName;

                $filePath = File::symbolizePath($viewTemplate);
                if (!File::isFile($filePath)) {
                    throw new ValidationException([
                        'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_view_file_not_found', [
                            'class'=>$behaviorClass,
                            'view'=>$templateFileName
                        ])
                    ]);
                }

                $destFilePath = $controllerPath.'/'.$templateBaseName;
                if (File::isFile($destFilePath)) {
                    throw new ValidationException([
                        'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_view_file_exists', [
                            'view'=>$destFilePath
                        ])
                    ]);
                }
            }
        }
    }

    protected function validateBehaviorConfigTemplates()
    {
        if (!$this->sourceModel->behaviors) {
            return;
        }

        $this->configTemplateProperties = [];

        $controllerPath = $this->sourceModel->getControllerFilePath(true);
        $behaviorLibrary = ControllerBehaviorLibrary::instance();

        $knownTemplates = [];
        foreach ($this->sourceModel->behaviors as $behaviorClass) {
            $behaviorInfo = $behaviorLibrary->getBehaviorInfo($behaviorClass);
            $configTemplate = $behaviorInfo['configTemplate'];

            if (!strlen($configTemplate)) {
                continue;
            }

            $templateFileName = basename($configTemplate);
            $templateBaseName = pathinfo($templateFileName, PATHINFO_FILENAME);

            if (in_array($templateFileName, $knownTemplates)) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_config_conflict', [
                        'file'=> $templateBaseName
                    ])
                ]);

                throw new ApplicationException();
            }

            $knownTemplates[] = $templateFileName;

            $filePath = File::symbolizePath($configTemplate);
            if (!File::isFile($filePath)) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_config_file_not_found', [
                        'class'=>$behaviorClass,
                        'file'=>$templateFileName
                    ])
                ]);
            }

            $destFilePath = $controllerPath.'/'.$templateBaseName;
            if (File::isFile($destFilePath)) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_config_file_exists', [
                        'file'=>$destFilePath
                    ])
                ]);
            }

            $configPropertyName = $behaviorInfo['configPropertyName'];
            $this->configTemplateProperties[$configPropertyName] = $templateBaseName;
        }
    }

    protected function validateControllerUnique()
    {
        $controlerFilePath = $this->sourceModel->getControllerFilePath();

        if (File::isFile($controlerFilePath)) {
            throw new ValidationException([
                'controller' => Lang::get('rainlab.builder::lang.controller.error_controller_exists', ['file'=>basename($controlerFilePath)])
            ]);
        }
    }

    protected function setTemplateVars()
    {
        $this->templateVars = [];

        $pluginCodeObj = $this->sourceModel->getPluginCodeObj();

        $this->templateVars['pluginNamespace'] = $pluginCodeObj->toPluginNamespace();
        $this->templateVars['pluginCode'] = $pluginCodeObj->toCode();
        $this->templateVars['permissions'] = $this->sourceModel->permissions;
        $this->templateVars['controller'] = $this->sourceModel->controller;
        $this->templateVars['baseModelClassName'] = $this->sourceModel->baseModelClassName;

        $menuItem = $this->sourceModel->menuItem;
        if ($menuItem) {
            $itemParts = explode('||', $menuItem);
            $this->templateVars['menuItem'] = $itemParts[0];

            if (count($itemParts) > 1) {
                $this->templateVars['sideMenuItem'] = $itemParts[1];
            }
        }

        if ($this->sourceModel->behaviors) {
            $this->templateVars['behaviors'] = $this->sourceModel->behaviors;

        }
        else {
            $this->templateVars['behaviors'] = [];
        }

        $this->templateVars['behaviorConfigVars'] = $this->configTemplateProperties;
    }

    protected function getTemplatePath($template)
    {
        return __DIR__.'/controllergenerator/templates/'.$template;
    }

    protected function parseTemplate($templatePath, $vars = [])
    {
        $template = File::get($templatePath);

        $vars = array_merge($this->templateVars, $vars);
        $code = Twig::parse($template, $vars);

        return $code;
    }

    protected function writeFile($path, $data)
    {
        $fileDirectory = dirname($path);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_make_dir', ['name'=>$fileDirectory]));
            }
        }

        if (@File::put($path, $data) === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_save_file', ['file'=>basename($path)]));
        }

        @File::chmod($path);
        $this->filesGenerated[] = $path;
    }

    protected function rollback()
    {
        foreach ($this->filesGenerated as $path) {
            @unlink($path);
        }
    }

    protected function generateControllerFile()
    {
        $templateParts = [];
        $code = $this->parseTemplate($this->getTemplatePath('controller-config-vars.php.tpl'));
        if (strlen($code)) {
            $templateParts[] = $code;
        }

        $code = $this->parseTemplate($this->getTemplatePath('controller-permissions.php.tpl'));
        if (strlen($code)) {
            $templateParts[] = $code;
        }

        if (count($templateParts)) {
            $templateParts = "\n".implode("\n", $templateParts);
        } 
        else {
            $templateParts = "";
        }

        $code = $this->parseTemplate($this->getTemplatePath('controller.php.tpl'), [
            'templateParts' => $templateParts
        ]);

        $controlerFilePath = $this->sourceModel->getControllerFilePath();

        $this->writeFile($controlerFilePath, $code);
    }

    protected function generatConfigFiles()
    {
        $controllerPath = $this->sourceModel->getControllerFilePath(true);
        $behaviorLibrary = ControllerBehaviorLibrary::instance();

        foreach ($this->sourceModel->behaviors as $behaviorClass) {
            $behaviorInfo = $behaviorLibrary->getBehaviorInfo($behaviorClass);
            $configTemplate = $behaviorInfo['configTemplate'];

            if (!strlen($configTemplate)) {
                continue;
            }

            $filePath = File::symbolizePath($configTemplate);
            $code = $this->parseTemplate($filePath);

            $templateBaseName = pathinfo($configTemplate, PATHINFO_FILENAME);
            $destFilePath = $controllerPath.'/'.$templateBaseName;

            $this->writeFile($destFilePath, $code);
        }
    }

}