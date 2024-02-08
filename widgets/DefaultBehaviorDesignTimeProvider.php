<?php namespace RainLab\Builder\Widgets;

use Lang;
use RainLab\Builder\Classes\BehaviorDesignTimeProviderBase;
use RainLab\Builder\Models\ModelListModel;
use RainLab\Builder\Models\ModelFormModel;
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
        \Backend\Behaviors\FormController::class => 'form-controller',
        \Backend\Behaviors\ListController::class => 'list-controller',
        \Backend\Behaviors\ImportExportController::class => 'import-export-controller'
    ];

    /**
     * Renders behavior body.
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
            case \Backend\Behaviors\FormController::class:
                return $this->getFormControllerDefaultConfiguration($controllerModel, $controllerGenerator);
            case \Backend\Behaviors\ListController::class:
                return $this->getListControllerDefaultConfiguration($controllerModel, $controllerGenerator);
            case \Backend\Behaviors\ImportExportController::class:
                return $this->getImportExportControllerDefaultConfiguration($controllerModel, $controllerGenerator);
        }
    }

    /**
     * renderUnknownControl
     */
    protected function renderUnknownControl($class, $properties)
    {
        return $this->makePartial('behavior-unknown', [
            'properties'=>$properties,
            'class'=>$class
        ]);
    }

    /**
     * getFormControllerDefaultConfiguration
     */
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

        $controllerUrl = $this->getControllerUrl($pluginCodeObj, $controllerModel->controller);

        $result = [
            'name' => $controllerModel->controllerName,
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

    /**
     * getListControllerDefaultConfiguration
     */
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
            'recordsPerPage' => 20,
            'toolbar' => [
                'buttons' => 'list_toolbar',
                'search' => [
                    'prompt' => 'backend::lang.list.search_prompt'
                ]
            ]
        ];

        if (array_key_exists(\Backend\Behaviors\FormController::class, $controllerModel->behaviors)) {
            $updateUrl = $this->getControllerUrl($pluginCodeObj, $controllerModel->controller).'/update/:id';
            $createUrl = $this->getControllerUrl($pluginCodeObj, $controllerModel->controller).'/create';

            $result['recordUrl'] = $updateUrl;

            $controllerGenerator->setTemplateVariable('hasFormBehavior', true);
            $controllerGenerator->setTemplateVariable('createUrl', $createUrl);
        }

        if (in_array(\Backend\Behaviors\ImportExportController::class, $controllerModel->behaviors)) {
            $importUrl = $this->getControllerUrl($pluginCodeObj, $controllerModel->controller).'/import';
            $exportUrl = $this->getControllerUrl($pluginCodeObj, $controllerModel->controller).'/export';
            $controllerGenerator->setTemplateVariable('hasImportExportBehavior', true);
            $controllerGenerator->setTemplateVariable('importUrl', $importUrl);
            $controllerGenerator->setTemplateVariable('exportUrl', $exportUrl);
        }

        return $result;
    }

    /**
     * getImportExportControllerDefaultConfiguration
     */
    protected function getImportExportControllerDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_behavior_requires_base_model', [
                'behavior' => 'Import Export Controller'
            ]));
        }

        $pluginCodeObj = $controllerModel->getPluginCodeObj();

        $result = [
            'import.title' => $controllerModel->controller,
            'import.modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName),
            'export.title' => $controllerModel->controller,
            'export.modelClass' => $this->getFullModelClass($pluginCodeObj, $controllerModel->baseModelClassName),
        ];

        return $result;
    }

    /**
     * getFullModelClass
     */
    protected function getFullModelClass($pluginCodeObj, $modelClassName)
    {
        return $pluginCodeObj->toPluginNamespace().'\\Models\\'.$modelClassName;
    }

    /**
     * getModelFilePath
     */
    protected function getModelFilePath($pluginCodeObj, $modelClassName, $file)
    {
        return '$/' . $pluginCodeObj->toFilesystemPath() . '/models/' . strtolower($modelClassName) . '/' . $file;
    }

    /**
     * getControllerUrl
     */
    protected function getControllerUrl($pluginCodeObj, $controller)
    {
        return $pluginCodeObj->toUrl().'/'.strtolower($controller);
    }
}
