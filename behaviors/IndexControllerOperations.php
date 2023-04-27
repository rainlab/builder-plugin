<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\ControllerModel;
use RainLab\Builder\Classes\PluginCode;
use Request;
use Flash;
use Input;
use Lang;

/**
 * IndexControllerOperations is plugin controller management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexControllerOperations extends IndexOperationsBehaviorBase
{
    /**
     * @var string baseFormConfigFile
     */
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/controllermodel/fields.yaml';

    /**
     * onControllerOpen
     */
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

    /**
     * onControllerCreate
     */
    public function onControllerCreate()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $model = $this->loadOrCreateBaseModel(null, $options);
        $model->fill(post());
        $model->save();

        $this->vars['controller'] = $model->controller;

        $result = $this->controller->widget->controllerList->updateList();

        if ($model->behaviors) {
            // Create a new tab only for controllers with behaviors.
            $widget = $this->makeBaseFormWidget($model->controller, $options);

            $tab = [
                'tabTitle' => $this->getTabName($widget->model),
                'tabIcon' => 'icon-asterisk',
                'tabId' => $this->getTabId($pluginCodeObj->toCode(), $model->controller),
                'tab' => $this->makePartial('tab', [
                    'form'  => $widget,
                    'pluginCode' => $pluginCodeObj->toCode()
                ])
            ];

            $result = array_merge($result, $tab);
        }

        $this->mergeRegistryDataIntoResult($result, $pluginCodeObj);

        return $result;
    }

    /**
     * onControllerSave
     */
    public function onControllerSave()
    {
        $controller = Input::get('controller');

        $model = $this->loadModelFromPost();
        $model->fill(post());
        $model->save();

        Flash::success(Lang::get('rainlab.builder::lang.controller.saved'));

        $result['builderResponseData'] = [];

        return $result;
    }

    /**
     * onControllerShowCreatePopup
     */
    public function onControllerShowCreatePopup()
    {
        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $this->baseFormConfigFile = '~/plugins/rainlab/builder/models/controllermodel/fields_new_controller.yaml';
        $widget = $this->makeBaseFormWidget(null, $options);

        return $this->makePartial('create-controller-popup-form', [
            'form' => $widget,
            'pluginCode' => $pluginCodeObj->toCode()
        ]);
    }

    /**
     * getTabName
     */
    protected function getTabName($model)
    {
        $pluginName = Lang::get($model->getModelPluginName());

        return $pluginName.'/'.$model->controller;
    }

    /**
     * getTabId
     */
    protected function getTabId($pluginCode, $controller)
    {
        return 'controller-'.$pluginCode.'-'.$controller;
    }

    /**
     * loadModelFromPost
     */
    protected function loadModelFromPost()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));
        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $controller = Input::get('controller');

        return $this->loadOrCreateBaseModel($controller, $options);
    }

    /**
     * loadOrCreateBaseModel
     */
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

    /**
     * mergeRegistryDataIntoResult
     */
    protected function mergeRegistryDataIntoResult(&$result, $pluginCodeObj)
    {
        if (!array_key_exists('builderResponseData', $result)) {
            $result['builderResponseData'] = [];
        }

        $pluginCode = $pluginCodeObj->toCode();
        $result['builderResponseData']['registryData'] = [
            'urls' => ControllerModel::getPluginRegistryData($pluginCode, null),
            'pluginCode' => $pluginCode
        ];
    }
}
