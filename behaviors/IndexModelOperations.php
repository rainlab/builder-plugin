<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\ModelModel;
use Backend\Behaviors\FormController;
use ApplicationException;
use Exception;
use Request;
use Input;

/**
 * Model management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexModelOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/modelmodel/fields.yaml';

    public function onModelLoadPopup()
    {
        $pluginCodeObj = $this->getPluginCode();

        try {
            $widget = $this->makeBaseFormWidget(null);
            $this->vars['form'] = $widget;
            $widget->model->setPluginCodeObj($pluginCodeObj);
            $this->vars['pluginCode'] = $pluginCodeObj->toCode();
        }
        catch (ApplicationException $ex) {
            $this->vars['errorMessage'] = $ex->getMessage();
        }

        return $this->makePartial('model-popup-form');
    }

    public function onModelSave()
    {
        $pluginCode = Request::input('plugin_code');

        $model = $this->loadOrCreateBaseModel(null);
        $model->setPluginCode($pluginCode);

        $model->fill(post());
        $model->save();

        $result = $this->controller->widget->modelList->updateList();

        $builderResponseData = [
            'registryData' => [
                'models' => ModelModel::getPluginRegistryData($pluginCode, null),
                'pluginCode' => $pluginCode
            ]
        ];

        $result['builderResponseData'] = $builderResponseData;

        return $result;
    }

    protected function loadOrCreateBaseModel($className, $options = [])
    {
        // Editing model is not supported, always return
        // a new object.

        return new ModelModel();
    }
}
