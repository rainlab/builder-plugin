<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\ModelFormModel;
use RainLab\Builder\Classes\PluginCode;
use RainLab\Builder\FormWidgets\FormBuilder;
use RainLab\Builder\Classes\ModelModel;
use Backend\Classes\FormField;
use ApplicationException;
use Exception;
use Request;
use Flash;
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
        $modelClass = Input::get('model_class');

        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode(),
            'modelClass' => $modelClass
        ];

        $widget = $this->makeBaseFormWidget($fileName, $options);
        $this->vars['fileName'] = $fileName;

        $result = [
            'tabTitle' => $widget->model->getDisplayName(Lang::get('rainlab.builder::lang.form.tab_new_form')),
            'tabIcon' => 'oc-icon-check-square',
            'tabId' => $this->getTabId($modelClass, $fileName),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode(),
                'fileName' => $fileName,
                'modelClass' => $modelClass
            ])
        ];

        return $result;
    }

    public function onModelFormSave()
    {
        $model = $this->loadOrCreateFormFromPost();

        $model->fill($_POST);
        $model->save();

        $result = $this->controller->widget->modelList->updateList();

        Flash::success(Lang::get('rainlab.builder::lang.form.saved'));

        $modelClass = Input::get('model_class');
        $result['builderRepsonseData'] = [
            'builderObjectName' => $model->fileName,
            'tabId' => $this->getTabId($modelClass, $model->fileName),
            'tabTitle' => $model->getDisplayName(Lang::get('rainlab.builder::lang.form.tab_new_form'))
        ];

        return $result;
    }

    public function onModelFormDelete()
    {
        $model = $this->loadOrCreateFormFromPost();

        $model->deleteModel();

        return $this->controller->widget->modelList->updateList();
    }

    public function onModelFormGetModelFields()
    {
        $columnNames = ModelModel::getModelFields($this->getPluginCode(), Input::get('model_class'));

        $result = [];
        foreach ($columnNames as $columnName) {
            $result[] = [
                'title' => $columnName,
                'value' => $columnName
            ];
        }

        return [
            'responseData' => [
                'options' => $result
            ]
        ];
    }

    protected function loadOrCreateFormFromPost()
    {
        $pluginCode = Request::input('plugin_code');
        $modelClass = Input::get('model_class');
        $fileName = Input::get('file_name');

        $options = [
            'pluginCode' => $pluginCode,
            'modelClass' => $modelClass
        ];

        return $this->loadOrCreateBaseModel($fileName, $options);
    }

    protected function getTabId($modelClass, $fileName)
    {
        if (!strlen($fileName)) {
            return 'modelForm-'.uniqid(time());
        }

        return 'modelForm-'.$modelClass.'-'.$fileName;
    }

    protected function loadOrCreateBaseModel($fileName, $options = [])
    {
        $model = new ModelFormModel();

        if (isset($options['pluginCode']) && isset($options['modelClass'])) {
            $model->setPluginCode($options['pluginCode']);
            $model->setModelClassName($options['modelClass']);
        }

        if (!$fileName) {
            return $model;
        }

        $model->loadForm($fileName);
        return $model;
    }
}