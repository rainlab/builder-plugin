<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\PluginBaseModel;
use Backend\Behaviors\FormController;
use ApplicationException;
use Exception;
use Input;

/**
 * Plugin management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexPluginOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/pluginbasemodel/fields.yaml';

    public function onPluginLoadPopup()
    {
        $pluginCode = Input::get('pluginCode');

        try {
            $this->vars['form'] = $this->makeBaseFormWidget($pluginCode);
            $this->vars['pluginCode'] = $pluginCode;
        }
        catch (ApplicationException $ex) {
            $this->vars['errorMessage'] = $ex->getMessage();
        }

        return $this->makePartial('plugin-popup-form');
    }

    public function onPluginSave()
    {
        $pluginCode = Input::get('pluginCode');

        $model = $this->loadOrCreateBaseModel($pluginCode);
        $model->fill(post());
        $model->save();

        if (!$pluginCode) {
            $result = [];

            $result['responseData'] = [
                'pluginCode' => $model->getPluginCode(),
                'isNewPlugin' => 1
            ];

            return $result;
        } else {
            $result = [];

            $result['responseData'] = [
                'pluginCode' => $model->getPluginCode()
            ];

            return array_merge($result, $this->controller->updatePluginList());
        }
    }

    public function onPluginSetActive()
    {
        $pluginCode = Input::get('pluginCode');
        $updatePluginList = Input::get('updatePluginList');

        $result = $this->controller->setBuilderActivePlugin($pluginCode, false);

        if ($updatePluginList) {
            $result = array_merge($result, $this->controller->updatePluginList());
        }

        $result['responseData'] = ['pluginCode'=>$pluginCode];

        return $result;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new PluginBaseModel();

        if (!$pluginCode) {
            $model->initDefaults();
            return $model;
        }

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
