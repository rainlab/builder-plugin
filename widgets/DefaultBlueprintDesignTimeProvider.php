<?php namespace RainLab\Builder\Widgets;

use Lang;
use RainLab\Builder\Classes\BlueprintDesignTimeProviderBase;
use RainLab\Builder\Classes\ModelListModel;
use RainLab\Builder\Classes\ModelFormModel;
use SystemException;
use ApplicationException;

/**
 * Default blueprint design-time provider.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DefaultBlueprintDesignTimeProvider extends BlueprintDesignTimeProviderBase
{
    protected $defaultBlueprintClasses = [
        'Tailor\Classes\Blueprint\EntryBlueprint' => 'entry',
        'Tailor\Classes\Blueprint\GlobalBlueprint' => 'global',
    ];

    /**
     * Renders behaivor body.
     * @param string $class Specifies the blueprint class to render.
     * @param array $properties Blueprint property values.
     * @param  \RainLab\Builder\FormWidgets\BlueprintBuilder $blueprintBuilder BlueprintBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderBlueprintBody($class, $properties, $blueprintBuilder)
    {
        if (!array_key_exists($class, $this->defaultBlueprintClasses)) {
            return $this->renderUnknownBlueprint($class, $properties);
        }

        $partial = $this->defaultBlueprintClasses[$class];

        return $this->makePartial('blueprint-'.$partial, [
            'properties' => $properties,
            'blueprintBuilder' => $blueprintBuilder
        ]);
    }

    /**
     * Returns default blueprint configuration as an array.
     * @param string $class Specifies the blueprint class name.
     * @param string $controllerModel Controller model.
     * @param mixed $controllerGenerator Controller generator object.
     * @return array Returns the blueprint configuration array.
     */
    public function getDefaultConfiguration($class, $controllerModel, $controllerGenerator)
    {
        if (!array_key_exists($class, $this->defaultBlueprintClasses)) {
            throw new SystemException('Unknown blueprint class: '.$class);
        }

        switch ($class) {
            case 'Tailor\Classes\Blueprint\EntryBlueprint':
                return $this->getEntryBlueprintDefaultConfiguration($controllerModel, $controllerGenerator);
            case 'Tailor\Classes\Blueprint\GlobalBlueprint':
                return $this->getGlobalBlueprintDefaultConfiguration($controllerModel, $controllerGenerator);
        }
    }

    protected function renderUnknownControl($class, $properties)
    {
        return $this->makePartial('blueprint-unknown', [
            'properties'=>$properties,
            'class'=>$class
        ]);
    }

    protected function getEntryBlueprintDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_blueprint_requires_base_model', [
                'blueprint' => 'Form Controller'
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

    protected function getGlobalBlueprintDefaultConfiguration($controllerModel, $controllerGenerator)
    {
        if (!$controllerModel->baseModelClassName) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_blueprint_requires_base_model', [
                'blueprint' => 'List Controller'
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
     * getControllerlUrl
     */
    protected function getControllerlUrl($pluginCodeObj, $controller)
    {
         return $pluginCodeObj->toUrl().'/'.strtolower($controller);
    }
}
