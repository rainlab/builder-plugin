<?php namespace RainLab\Builder\Widgets;

use Lang;
use RainLab\Builder\Classes\BehaviorDesignTimeProviderBase;
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
        'Backend\Behaviors\ReorderController' => 'reorder-controller'
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
}