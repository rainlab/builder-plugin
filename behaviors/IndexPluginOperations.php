<?php namespace RainLab\Builder\Behaviors;

use Backend\Classes\ControllerBehavior;
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
class IndexPluginOperations extends ControllerBehavior
{
    public function onPluginLoadPopup()
    {
        $pluginCode = Input::get('pluginCode');

        try {
            $this->vars['form'] = $this->makePluginBaseFormWidget($pluginCode);
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

        $model = $this->loadOrCreatePluginModel($pluginCode);
        $model->fill($_POST);
        $model->save();

        if (!$pluginCode) {
            return $this->controller->setBuilderActivePlugin($model->getPluginCode(), true);
        } else {
            return $this->controller->updatePluginList();
        }
    }

    protected function makePluginBaseFormWidget($pluginCode)
    {
        $formConfig = '~/plugins/rainlab/builder/classes/pluginbasemodel/fields.yaml';
        $widgetConfig = $this->makeConfig($formConfig);

        $widgetConfig->model = $this->loadOrCreatePluginModel($pluginCode);
        $widgetConfig->alias = 'form_plugin_'.uniqid();

        $form = $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
        $form->context = $pluginCode ? FormController::CONTEXT_UPDATE : FormController::CONTEXT_CREATE;

        return $form;
    }

    protected function loadOrCreatePluginModel($pluginCode)
    {
        $pluginModel = new PluginBaseModel();

        if (!$pluginCode) {
            $pluginModel->initDefaults();
            return $pluginModel;
        }

        $pluginModel->loadPlugin($pluginCode);
        return $pluginModel;
    }

}