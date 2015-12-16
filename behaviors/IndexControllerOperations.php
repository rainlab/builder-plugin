<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\ControllerModel;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin controller management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexControllerOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/controllermodel/fields.yaml';

    public function onControllerOpen()
    {
        $controller = Input::get('controller');
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $widget = $this->makeBaseFormWidget($controller, $options);
        $this->vars['controller'] = $controller;

        $result = [
            'tabTitle' => $this->getTabName($widget->model),
            'tabIcon' => 'icon-asterisk',
            'tabId' => $this->getTabId($pluginCodeObj->toCode(), $controller),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onControllerSave()
    {
        // $model = $this->loadOrCreateLocalizationFromPost();
        // $model->fill($_POST);
        // $model->save(false);

        // Flash::success(Lang::get('rainlab.builder::lang.localization.saved'));
        // $result = $this->controller->widget->languageList->updateList();

        // $result['builderRepsonseData'] = [
        //     'tabId' => $this->getTabId($model->getPluginCodeObj()->toCode(), $model->language),
        //     'tabTitle' => $this->getTabName($model),
        //     'language' => $model->language
        // ];

        // return $result;
    }

    protected function getTabName($model)
    {
        $pluginName = $model->getModelPluginName();

        return $pluginName.'/'.$model->controller;
    }

    protected function getTabId($pluginCode, $controller)
    {
        return 'controller-'.$pluginCode.'-'.$controller;
    }

    protected function loadOrCreateBaseModel($controller, $options = [])
    {
        $model = new ControllerModel();

        if (isset($options['pluginCode'])) {
            $model->setPluginCode($options['pluginCode']);
        }

        if (!$controller) {
            return $model;
        }

        $model->load($controller);
        return $model;
    }
}