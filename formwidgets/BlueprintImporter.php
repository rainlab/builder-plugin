<?php namespace RainLab\Builder\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Input;
use Lang;

/**
 * BlueprintImporter form widget
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class BlueprintImporter extends FormWidgetBase
{
    protected $iconList = null;

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'blueprintimporter';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('body');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['model'] = $this->model;
        $this->vars['items'] = $this->model->blueprints;

        $this->vars['emptyItem'] = [
            'label' => __("Add Blueprint"),
            'icon' => 'icon-life-ring',
            'code' => 'newitemcode',
            'url' => '/'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/blueprintimporter.js', 'builder');
    }

    /**
     * getPluginCode
     */
    public function getPluginCode()
    {
        $pluginCode = Input::get('plugin_code');
        if (strlen($pluginCode)) {
            return $pluginCode;
        }

        $pluginVector = $this->controller->getBuilderActivePluginVector();

        return $pluginVector->pluginCodeObj->toCode();
    }
}
