<?php namespace RainLab\Builder\Widgets;

use File;
use Lang;
use Twig;
use RainLab\Builder\Classes\BehaviorDesignTimeProviderBase;
use RainLab\Builder\Classes\ModelFileParser;
use RainLab\Builder\Classes\ModelListModel;
use RainLab\Builder\Classes\ModelFormModel;
use SystemException;
use ApplicationException;

/**
 * Default behavior design-time provider.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DefaultBehaviorDesignTimeProvider extends BehaviorDesignTimeProviderBase
{
    protected $defaultBehaviorClasses = [
        'Backend\Behaviors\FormController' => 'form-controller',
        'Backend\Behaviors\ListController' => 'list-controller',
        'Backend\Behaviors\ReorderController' => 'reorder-controller',
        'Backend\Behaviors\ImportExportController' => 'import-export-controller'
    ];

    /**
     * Renders behaivor body.
     * @param string $class Specifies the behavior class to render.
     * @param array $properties Behavior property values.
     * @param  \RainLab\Builder\FormWidgets\ControllerBuilder $controllerBuilder ControllerBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderBehaviorBody($class, $properties, $controllerBuilder)
    {
        if (!array_key_exists($class, $this->defaultBehaviorClasses)) {
            return $this->renderUnknownBehavior($class, $properties);
        }

        $partial = $this->defaultBehaviorClasses[$class];

        return $this->makePartial('behavior-'.$partial, [
            'properties'=>$properties,
            'controllerBuilder' => $controllerBuilder
        ]);
    }

    /**
     * Returns default behavior configuration as an array.
     * @param string $class Specifies the behavior class name.
     * @param string $controllerModel Controller model.
     * @param mixed $controllerGenerator Controller generator object.
     * @return array Returns the behavior configuration array.
     */
    public function getDefaultConfiguration($class, $controllerModel, $controllerGenerator)
    {
        if (!array_key_exists($class, $this->defaultBehaviorClasses)) {
            throw new SystemException('Unknown behavior class: '.$class);
        }

        switch ($class) {
            case 'Backend\Behaviors\FormController' : 
                return $this->getFormControllerDefaultConfiguration($controllerModel, $controllerGenerator);
            case 'Backend\Behaviors\ListController' : 
                return $this->getListControllerDefaultConfiguration($controllerModel, $controllerGenerator);
            case 'Backend\Behaviors\ReorderController' :
                return $this->getReorderControllerDefaultConfiguration($controllerModel, $controllerGenerator);
            case 'Backend\Behaviors\ImportExportController' :
                return $this->getImportExportControllerDefaultConfiguration($controllerModel, $controllerGenerator);
        }
    }

    protected function renderUnknownControl($class, $properties)
    {
        return $this->makePartial('behavior-unknown', [
            'properties'=>$properties,
            'class'=>$class
        ]);
    }

    protected function getFormControllerDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_behavior_requires_base_model', [
                'behavior' => 'Form Controller'
            ]));
        }

        $pluginCodeObj = $controllerModel->getPluginCodeObj();

        $forms = ModelFormModel::listModelFiles($pluginCodeObj, $controllerModel->baseModelClassName);
        if (!$forms) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_model_doesnt_have_forms'));
        }

        $controllerUrl = $this->getControllerlUrl($pluginCodeObj, $controllerModel->controller);

        $result = [
            'name' => $controllerModel->controller,
            'form' => $this->getModelFilePath($pluginCodeObj, $controllerModel->baseModelClassName, $forms[0]),
            'modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName),
            'defaultRedirect' => $controllerUrl,
            'create' => [
                'redirect' => $controllerUrl.'/update/:id',
                'redirectClose' => $controllerUrl
            ],
            'update' => [
                'redirect' => $controllerUrl,
                'redirectClose' => $controllerUrl
            ]
        ];

        return $result;
    } 

    protected function getListControllerDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_behavior_requires_base_model', [
                'behavior' => 'List Controller'
            ]));
        }

        $pluginCodeObj = $controllerModel->getPluginCodeObj();

        $lists = ModelListModel::listModelFiles($pluginCodeObj, $controllerModel->baseModelClassName);
        if (!$lists) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_model_doesnt_have_lists'));
        }

        $result = [
            'list' => $this->getModelFilePath($pluginCodeObj, $controllerModel->baseModelClassName, $lists[0]),
            'modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName),
            'title' => $controllerModel->controller,
            'noRecordsMessage' => 'backend::lang.list.no_records',
            'showSetup' => true,
            'showCheckboxes' => true,
            'toolbar' => [
                'buttons' => 'list_toolbar',
                'search' => [
                    'prompt' => 'backend::lang.list.search_prompt'
                ]
            ]
        ];

        if (in_array('Backend\Behaviors\FormController', $controllerModel->behaviors)) {
            $updateUrl = $this->getControllerlUrl($pluginCodeObj, $controllerModel->controller).'/update/:id';
            $createUrl = $this->getControllerlUrl($pluginCodeObj, $controllerModel->controller).'/create';

            $result['recordUrl'] = $updateUrl;

            $controllerGenerator->setTemplateVariable('hasFormBehavior', true);
            $controllerGenerator->setTemplateVariable('createUrl', $createUrl);
        }

        return $result;
    }

    protected function getReorderControllerDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_behavior_requires_base_model', [
                'behavior' => 'Reorder Controller'
            ]));
        }

        $pluginCodeObj = $controllerModel->getPluginCodeObj();

        $result = [
            'title' => $controllerModel->controller,
            'modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName),
            'toolbar' => [
                'buttons' => 'reorder_toolbar',
            ]
        ];

        if (in_array('Backend\Behaviors\ReorderController', $controllerModel->behaviors)) {
            $reorderUrl = $this->getControllerlUrl($pluginCodeObj, $controllerModel->controller).'/reorder';

            $result['reorderUrl'] = $reorderUrl;

            $controllerGenerator->setTemplateVariable('hasReorderBehavior', true);
            $controllerGenerator->setTemplateVariable('reorderUrl', $reorderUrl);
        }

        return $result;
    }

    protected function getImportExportControllerDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_behavior_requires_base_model', [
                'behavior' => 'Import / Export Controller'
            ]));
        }

        $pluginCodeObj = $controllerModel->getPluginCodeObj();

        $lists = ModelListModel::listModelFiles($pluginCodeObj, $controllerModel->baseModelClassName);
        if (!$lists) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_model_doesnt_have_lists'));
        }

        $result = [
            'title' => $controllerModel->controller,
            'noRecordsMessage' => 'backend::lang.list.no_records',
            'import' => [
                'list' => $this->getModelFilePath($pluginCodeObj, $controllerModel->baseModelClassName, $lists[0]),
                'modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName)
            ],
            'export' => [
                'list' => $this->getModelFilePath($pluginCodeObj, $controllerModel->baseModelClassName, $lists[0]),
                'modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName)
            ],
            'toolbar' => [
                'buttons' => 'import_export_toolbar'
            ]
        ];

        if (in_array('Backend\Behaviors\ImportExportController', $controllerModel->behaviors)) {

            $importUrl = $this->getControllerlUrl($pluginCodeObj, $controllerModel->controller).'/import';
            $exportUrl = $this->getControllerlUrl($pluginCodeObj, $controllerModel->controller).'/export';

            $result['importUrl'] = $importUrl;
            $result['exportUrl'] = $exportUrl;

            $controllerGenerator->setTemplateVariable('hasImportExportBehavior', true);
            $controllerGenerator->setTemplateVariable('importUrl', $importUrl);
            $controllerGenerator->setTemplateVariable('exportUrl', $exportUrl);

            $parser = new ModelFileParser();
            $className = $controllerModel->baseModelClassName;
            $modelPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath() . '/models/' . $className . '.php');
            $namespace = $pluginCodeObj->toPluginNamespace() . '\Models';
            $table = $parser->extractModelInfoFromSource(File::get($modelPath))['table'];

            $importModelPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/models/Import' . $className . '.php');
            $importModelCode = $this->parseTemplate($this->getImportExportModelTemplatePath('importmodel', 'import_model.php.tpl'), [
                'namespace' => $namespace,
                'className' => $className,
                'table' => $table
            ]);

            $this->writeFile($importModelPath, $importModelCode);

            $exportModelPath = File::symbolizePath($pluginCodeObj->toPluginDirectoryPath().'/models/Export' . $className . '.php');
            $exportModelCode = $this->parseTemplate($this->getImportExportModelTemplatePath('exportmodel', 'export_model.php.tpl'), [
                'namespace' => $namespace,
                'className' => $className,
                'table' => $table
            ]);

            $this->writeFile($exportModelPath, $exportModelCode);
        }

        return $result;
    }

    protected function getFullModelClass($pluginCodeObj, $modelClassName)
    {
        return $pluginCodeObj->toPluginNamespace().'\\Models\\'.$modelClassName;
    }

    protected function getModelFilePath($pluginCodeObj, $modelClassName, $file)
    {
        return '$/' . $pluginCodeObj->toFilesystemPath() . '/models/' . strtolower($modelClassName) . '/' . $file;
    }

    protected function getControllerlUrl($pluginCodeObj, $controller)
    {
         return $pluginCodeObj->toUrl().'/'.strtolower($controller);
    }

    protected function parseTemplate($templatePath, $vars)
    {
        $template = File::get($templatePath);
        $code = Twig::parse($template, $vars);

        return $code;
    }

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
    }

    protected function getImportExportModelTemplatePath($directory, $template)
    {
        return __DIR__ . '/../classes/' . $directory . '/templates/'.$template;
    }
}