<?php namespace RainLab\Builder\FormWidgets;

use RainLab\Builder\Classes\ImportsModel;
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
     * @var \Backend\Classes\WidgetBase selectWidget reference to the widget used for selecting a page.
     */
    protected $selectFormWidget;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        if (post('blueprintimporter_flag')) {
            $this->getSelectFormWidget();
        }
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
        $this->vars['selectWidget'] = $this->getSelectFormWidget();
        $this->vars['pluginCode'] = $this->getPluginCode();

        $this->vars['emptyItem'] = [
            'label' => __("Add Blueprint"),
            'icon' => 'icon-life-ring',
            'code' => 'newitemcode',
            'url' => '/'
        ];
    }

    /**
     * onShowSelectBlueprintForm
     */
    public function onShowSelectBlueprintForm()
    {
        $this->prepareVars();

        return $this->makePartial('select_blueprint_form');
    }

    /**
     * onSelectBlueprint
     */
    public function onSelectBlueprint()
    {
        $widget = $this->getSelectFormWidget();

        $data = $widget->getSaveData();

        traceLog($data);
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


    /**
     * getSelectFormWidget
     */
    protected function getSelectFormWidget()
    {
        if ($this->selectFormWidget) {
            return $this->selectFormWidget;
        }

        $model = new ImportsModel;
        $config = $this->makeConfig('~/plugins/rainlab/builder/classes/importsmodel/fields_select.yaml');
        $config->model = $model;
        $config->alias = $this->alias . 'Select';
        $config->arrayName = 'BlueprintImporter';

        $form = $this->makeWidget(\Backend\Widgets\Form::class, $config);
        $form->bindToController();

        return $this->selectFormWidget = $form;
    }
}
