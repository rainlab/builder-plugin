<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ValidationException;
use Exception;
use Lang;
use File;
use Twig;

/**
 * ControllerGenerator is a helper class for generating controller class files and associated files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerGenerator
{
    /**
     * @var BaseModel sourceModel
     */
    protected $sourceModel;

    /**
     * @var array templateVars
     */
    protected $templateVars;

    /**
     * @var array configTemplateProperties
     */
    protected $configTemplateProperties = [];

    /**
     * @var array templateFiles
     */
    protected $templateFiles = [];

    /**
     * @var array filesGenerated
     */
    protected $filesGenerated;

    /**
     * @var array designTimeProviders
     */
    protected $designTimeProviders = [];

    /**
     * __construct
     */
    public function __construct($source)
    {
        $this->sourceModel = $source;
    }

    /**
     * generate
     */
    public function generate()
    {
        $this->filesGenerated = [];
        $this->templateVars = [];

        try {
            $this->validateBehaviorViewTemplates();
            $this->validateBehaviorConfigSettings();
            $this->validateControllerUnique();

            $this->setTemplateVars();
            $this->generateControllerFile();
            $this->generateConfigFiles();
            $this->generateViews();
        }
        catch (Exception $ex) {
            $this->rollback();

            throw $ex;
        }
    }

    /**
     * setTemplateVariable
     */
    public function setTemplateVariable($var, $value)
    {
        $this->templateVars[$var] = $value;
    }

    /**
     * validateBehaviorViewTemplates
     */
    protected function validateBehaviorViewTemplates()
    {
        if (!$this->sourceModel->behaviors) {
            return;
        }

        $this->templateFiles = [];

        $controllerPath = $this->sourceModel->getControllerFilePath(true);
        $behaviorLibrary = ControllerBehaviorLibrary::instance();

        $knownTemplates = [];
        foreach ($this->sourceModel->behaviors as $behaviorClass => $behaviorConfig) {
            $behaviorInfo = $behaviorLibrary->getBehaviorInfo($behaviorClass);
            if (!$behaviorInfo) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_unknown_behavior', [
                        'class' => $behaviorClass
                    ])
                ]);
            }

            foreach ($behaviorInfo['viewTemplates'] as $viewTemplate) {
                $templateFileName = basename($viewTemplate);
                $templateBaseName = pathinfo($templateFileName, PATHINFO_FILENAME);

                if (in_array($templateFileName, $knownTemplates)) {
                    throw new ValidationException([
                        'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_view_conflict', [
                            'view' => $templateBaseName
                        ])
                    ]);
                }

                $knownTemplates[] = $templateFileName;

                $filePath = File::symbolizePath($viewTemplate);
                if (!File::isFile($filePath)) {
                    throw new ValidationException([
                        'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_view_file_not_found', [
                            'class' => $behaviorClass,
                            'view' => $templateFileName
                        ])
                    ]);
                }

                $destFilePath = $controllerPath.'/'.$templateBaseName;
                if (File::isFile($destFilePath)) {
                    throw new ValidationException([
                        'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_view_file_exists', [
                            'view' => $destFilePath
                        ])
                    ]);
                }

                $this->templateFiles[$filePath] = $destFilePath;
            }
        }
    }

    /**
     * validateBehaviorConfigSettings
     */
    protected function validateBehaviorConfigSettings()
    {
        if (!$this->sourceModel->behaviors) {
            return;
        }

        $this->configTemplateProperties = [];

        $controllerPath = $this->sourceModel->getControllerFilePath(true);
        $behaviorLibrary = ControllerBehaviorLibrary::instance();

        $knownConfigFiles = [];
        foreach ($this->sourceModel->behaviors as $behaviorClass => $behaviorConfig) {
            $behaviorInfo = $behaviorLibrary->getBehaviorInfo($behaviorClass);
            $configFileName = $behaviorInfo['configFileName'];

            if (!strlen($configFileName)) {
                continue;
            }

            if (in_array($configFileName, $knownConfigFiles)) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_config_conflict', [
                        'file' => $configFileName
                    ])
                ]);
            }

            $knownConfigFiles[] = $configFileName;

            $destFilePath = $controllerPath.'/'.$configFileName;
            if (File::isFile($destFilePath)) {
                throw new ValidationException([
                    'behaviors' => Lang::get('rainlab.builder::lang.controller.error_behavior_config_file_exists', [
                        'file' => $destFilePath
                    ])
                ]);
            }

            $configPropertyName = $behaviorInfo['configPropertyName'];
            $this->configTemplateProperties[$configPropertyName] = $configFileName;
        }
    }

    /**
     * validateControllerUnique
     */
    protected function validateControllerUnique()
    {
        $controllerFilePath = $this->sourceModel->getControllerFilePath();

        if (File::isFile($controllerFilePath)) {
            throw new ValidationException([
                'controller' => Lang::get('rainlab.builder::lang.controller.error_controller_exists', [
                    'file' => basename($controllerFilePath)
                ])
            ]);
        }
    }

    /**
     * setTemplateVars
     */
    protected function setTemplateVars()
    {
        $pluginCodeObj = $this->sourceModel->getPluginCodeObj();

        $this->templateVars['pluginNamespace'] = $pluginCodeObj->toPluginNamespace();
        $this->templateVars['pluginCode'] = $pluginCodeObj->toCode();
        $this->templateVars['permissions'] = $this->sourceModel->permissions;
        $this->templateVars['controller'] = $this->sourceModel->controller;
        $this->templateVars['controllerName'] = $this->sourceModel->controllerName;
        $this->templateVars['baseModelClassName'] = $this->sourceModel->baseModelClassName;

        $this->templateVars['controllerUrl'] = $pluginCodeObj->toUrl().'/'.strtolower($this->sourceModel->controller);

        $menuItem = $this->sourceModel->menuItem;
        if ($menuItem) {
            $itemParts = explode('||', $menuItem);
            $this->templateVars['menuItem'] = $itemParts[0];

            if (count($itemParts) > 1) {
                $this->templateVars['sideMenuItem'] = $itemParts[1];
            }
        }

        if ($this->sourceModel->behaviors) {
            $this->templateVars['behaviors'] = array_keys($this->sourceModel->behaviors);
        }
        else {
            $this->templateVars['behaviors'] = [];
        }

        $this->templateVars['behaviorConfigVars'] = $this->configTemplateProperties;
    }

    /**
     * getTemplatePath
     */
    protected function getTemplatePath($template)
    {
        return __DIR__.'/controllergenerator/templates/'.$template;
    }

    /**
     * parseTemplate
     */
    protected function parseTemplate($templatePath, $vars = [])
    {
        $template = File::get($templatePath);

        $vars = array_merge($this->templateVars, $vars);
        $code = Twig::parse($template, $vars);

        return $code;
    }

    /**
     * writeFile
     */
    protected function writeFile($path, $data)
    {
        $fileDirectory = dirname($path);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_make_dir', [
                    'name' => $fileDirectory
                ]));
            }
        }

        if (@File::put($path, $data) === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_save_file', [
                'file' => basename($path)
            ]));
        }

        @File::chmod($path);
        $this->filesGenerated[] = $path;
    }

    /**
     * rollback
     */
    protected function rollback()
    {
        foreach ($this->filesGenerated as $path) {
            @unlink($path);
        }
    }

    /**
     * generateControllerFile
     */
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

        $noListTemplate = "";
        if (
            !array_key_exists(\Backend\Behaviors\ListController::class, $this->sourceModel->behaviors) &&
            array_key_exists(\Backend\Behaviors\FormController::class, $this->sourceModel->behaviors)
        ) {
            $noListTemplate = $this->parseTemplate($this->getTemplatePath('controller-no-list.php.tpl'));
        }

        $code = $this->parseTemplate($this->getTemplatePath('controller.php.tpl'), [
            'templateParts' => $templateParts,
            'noListTemplate' => $noListTemplate,
        ]);

        $controllerFilePath = $this->sourceModel->getControllerFilePath();

        $this->writeFile($controllerFilePath, $code);
    }

    /**
     * getBehaviorDesignTimeProvider
     */
    protected function getBehaviorDesignTimeProvider($providerClass)
    {
        if (array_key_exists($providerClass, $this->designTimeProviders)) {
            return $this->designTimeProviders[$providerClass];
        }

        return $this->designTimeProviders[$providerClass] = new $providerClass(null, []);
    }

    /**
     * generateConfigFiles
     */
    protected function generateConfigFiles()
    {
        if (!$this->sourceModel->behaviors) {
            return;
        }

        $controllerPath = $this->sourceModel->getControllerFilePath(true);
        $behaviorLibrary = ControllerBehaviorLibrary::instance();
        $dumper = new YamlDumper();

        foreach ($this->sourceModel->behaviors as $behaviorClass => $behaviorConfig) {
            $behaviorInfo = $behaviorLibrary->getBehaviorInfo($behaviorClass);
            $configFileName = $behaviorInfo['configFileName'];

            if (!strlen($configFileName)) {
                continue;
            }

            $provider = $this->getBehaviorDesignTimeProvider($behaviorInfo['designTimeProvider']);

            $destFilePath = $controllerPath.'/'.$configFileName;

            try {
                $configArray = $provider->getDefaultConfiguration($behaviorClass, $this->sourceModel, $this);
            }
            catch (Exception $ex) {
                throw new ValidationException(['baseModelClassName' => $ex->getMessage()]);
            }

            $configArray = array_merge($configArray, $behaviorConfig);

            $code = $dumper->dump($configArray, 20, 0, false, true);

            $this->writeFile($destFilePath, $code);
        }
    }

    /**
     * generateViews
     */
    protected function generateViews()
    {
        foreach ($this->templateFiles as $templatePath => $destPath) {
            $code = $this->parseTemplate($templatePath);

            $this->writeFile($destPath, $code);
        }
    }
}
