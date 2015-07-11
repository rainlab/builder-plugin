<?php namespace RainLab\Builder\Behaviors;

use Backend\Classes\ControllerBehavior;
use RainLab\Builder\Classes\PluginBaseModel;
use Backend\Behaviors\FormController;

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
        $this->vars['form'] = $this->makePluginBaseFormWidget(null);

        return $this->makePartial('plugin-popup-form');
    }

    public function onPluginSave()
    {
        $model = $this->loadOrCreatePluginModel(null);
        $model->fill($_POST);
        $model->save();
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

        // TODO - load the plugin here
    }

}