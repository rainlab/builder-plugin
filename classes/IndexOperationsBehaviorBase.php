<?php namespace RainLab\Builder\Classes;

use Backend\Classes\ControllerBehavior;
use Backend\Behaviors\FormController;
use ApplicationException;

/**
 * IndexOperationsBehaviorBase base class for index operation behaviors
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class IndexOperationsBehaviorBase extends ControllerBehavior
{
    /**
     * @var string|null baseFormConfigFile
     */
    protected $baseFormConfigFile = null;

    /**
     * makeBaseFormWidget
     */
    protected function makeBaseFormWidget($modelCode, $options = [])
    {
        if (!strlen($this->baseFormConfigFile)) {
            throw new ApplicationException(sprintf('Base form configuration file is not specified for %s behavior', get_class($this)));
        }

        $widgetConfig = $this->makeConfig($this->baseFormConfigFile);
        $widgetConfig->model = $this->loadOrCreateBaseModel($modelCode, $options);
        $widgetConfig->alias = 'form_'.md5(get_class($this)).uniqid();

        $widgetConfig = $this->extendBaseFormWidgetConfig($widgetConfig);

        $form = $this->makeWidget(\Backend\Widgets\Form::class, $widgetConfig);
        $form->context = strlen($modelCode) ? 'update' : 'create';

        return $form;
    }

    /**
     * extendBaseFormWidgetConfig
     */
    protected function extendBaseFormWidgetConfig($config)
    {
        return $config;
    }

    /**
     * getPluginCode
     */
    protected function getPluginCode()
    {
        $vector = $this->controller->getBuilderActivePluginVector();

        if (!$vector) {
            throw new ApplicationException('Cannot determine the currently active plugin.');
        }

        return $vector->pluginCodeObj;
    }

    /**
     * loadOrCreateBaseModel
     */
    abstract protected function loadOrCreateBaseModel($modelCode, $options = []);
}
