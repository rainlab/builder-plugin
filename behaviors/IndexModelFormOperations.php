<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\ModelFormModel;
use RainLab\Builder\Classes\PluginCode;
use RainLab\Builder\FormWidgets\FormBuilder;
use Backend\Classes\FormField;
use ApplicationException;
use Exception;
use Request;
use Input;
use Lang;

/**
 * Model form management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexModelFormOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/modelformmodel/fields.yaml';

    public function __construct($controller)
    {
        parent::__construct($controller);

        // Create the form builder instance to handle AJAX 
        // requests.
        $defaultBuilderField = new FormField('default', 'default');
        $formBulder = new FormBuilder($controller, $defaultBuilderField);
        $formBulder->alias = 'defaultFormBuilder';
        $formBulder->bindToController();
    }

    public function onModelFormCreateOrOpen()
    {
        $fileName = Input::get('file_name');
        $pluginCodeObj = $this->getPluginCode();

        $widget = $this->makeBaseFormWidget($fileName);
        $this->vars['fileName'] = $fileName;

        $result = [
            'tabTitle' => $this->getTabTitle($fileName),
            'tabIcon' => 'icon-check-square',
            'tabId' => $this->getTabId($fileName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'fileName' => $fileName
            ])
        ];

        return $result;
    }

    protected function getTabTitle($fileName)
    {
        if (!strlen($fileName)) {
            return Lang::get('rainlab.builder::lang.form.tab_new_form');
        }

        return $fileName;
    }

    protected function getTabId($fileName)
    {
        if (!strlen($fileName)) {
            return 'modelForm-'.uniqid(time());
        }

        return 'modelForm-'.$fileName;
    }

    protected function loadOrCreateBaseModel($fileName)
    {
        $model = new ModelFormModel();

        if (!$fileName) {
            return $model;
        }

        $model->load($fileName);
        return $model;
    }
}