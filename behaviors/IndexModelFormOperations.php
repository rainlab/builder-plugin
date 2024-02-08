<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\ModelFormModel;
use RainLab\Builder\FormWidgets\FormBuilder;
use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Classes\ControlLibrary;
use Backend\Classes\FormField;
use Backend\FormWidgets\DataTable;
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
    /**
     * @var string baseFormConfigFile
     */
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/modelformmodel/fields.yaml';

    /**
     * __construct
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        // Create the form builder instance to handle AJAX
        // requests.
        $defaultBuilderField = new FormField('default', 'default');
        $formBuilder = new FormBuilder($controller, $defaultBuilderField);
        $formBuilder->alias = 'defaultFormBuilder';
        $formBuilder->bindToController();
    }

    /**
     * onModelFormCreateOrOpen
     */
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
            'tabIcon' => 'icon-check-square',
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

    /**
     * onModelFormSave
     */
    public function onModelFormSave()
    {
        $model = $this->loadOrCreateFormFromPost();

        $model->fill(post());
        $model->save();

        $result = $this->controller->widget->modelList->updateList();

        Flash::success(Lang::get('rainlab.builder::lang.form.saved'));

        $modelClass = Input::get('model_class');
        $result['builderResponseData'] = [
            'builderObjectName' => $model->fileName,
            'tabId' => $this->getTabId($modelClass, $model->fileName),
            'tabTitle' => $model->getDisplayName(Lang::get('rainlab.builder::lang.form.tab_new_form'))
        ];

        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    /**
     * onModelFormDelete
     */
    public function onModelFormDelete()
    {
        $model = $this->loadOrCreateFormFromPost();

        $model->deleteModel();

        $result = $this->controller->widget->modelList->updateList();

        $modelClass = Input::get('model_class');
        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    /**
     * onModelFormGetModelFields
     */
    public function onModelFormGetModelFields()
    {
        $columnNames = ModelModel::getModelFields($this->getPluginCode(), Input::get('model_class'));
        $asPlainList = Input::get('as_plain_list');

        $result = [];
        foreach ($columnNames as $columnName) {
            if (!$asPlainList) {
                $result[] = [
                    'title' => $columnName,
                    'value' => $columnName
                ];
            }
            else {
                $result[$columnName] = $columnName;
            }
        }

        return [
            'responseData' => [
                'options' => $result
            ]
        ];
    }

    /**
     * onModelShowAddDatabaseFieldsPopup
     */
    public function onModelShowAddDatabaseFieldsPopup()
    {
        $columns = ModelModel::getModelColumnsAndTypes($this->getPluginCode(), Input::get('model_class'));
        $config = $this->makeConfig($this->getAddDatabaseFieldsDataTableConfig());

        $field = new FormField('add_database_fields_datatable', 'add_database_fields_datatable');
        $field->value = $this->getAddDatabaseFieldsDataTableValue($columns);

        $datatable = new DataTable($this->controller, $field, $config);
        $datatable->alias = 'add_database_fields_datatable';
        $datatable->bindToController();

        return $this->makePartial('add-database-fields-popup-form', [
            'datatable'  => $datatable,
            'pluginCode' => $this->getPluginCode()->toCode(),
        ]);
    }

    /**
     * loadOrCreateFormFromPost
     */
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

    /**
     * getTabId
     */
    protected function getTabId($modelClass, $fileName)
    {
        if (!strlen($fileName)) {
            return 'modelForm-'.uniqid(time());
        }

        return 'modelForm-'.$modelClass.'-'.$fileName;
    }

    /**
     * loadOrCreateBaseModel
     */
    protected function loadOrCreateBaseModel($fileName, $options = [])
    {
        $model = new ModelFormModel();

        if (isset($options['pluginCode']) && isset($options['modelClass'])) {
            $model->setPluginCode($options['pluginCode']);
            $model->setModelClassName($options['modelClass']);
        }

        if (!$fileName) {
            $model->initDefaults();

            return $model;
        }

        $model->loadForm($fileName);
        return $model;
    }

    /**
     * mergeRegistryDataIntoResult
     */
    protected function mergeRegistryDataIntoResult(&$result, $model, $modelClass)
    {
        if (!array_key_exists('builderResponseData', $result)) {
            $result['builderResponseData'] = [];
        }

        $fullClassName = $model->getPluginCodeObj()->toPluginNamespace().'\\Models\\'.$modelClass;
        $pluginCode = $model->getPluginCodeObj()->toCode();
        $result['builderResponseData']['registryData'] = [
            'forms' => ModelFormModel::getPluginRegistryData($pluginCode, $modelClass),
            'pluginCode' => $pluginCode,
            'modelClass' => $fullClassName
        ];
    }

    /**
     * getAddDatabaseFieldsDataTableConfig returns the configuration for the DataTable widget
     * that is used in the "add database fields" popup.
     *
     * @return array
     */
    protected function getAddDatabaseFieldsDataTableConfig()
    {
        // Get all registered controls and build an array that uses the control types as key and value for each entry.
        $controls = ControlLibrary::instance()->listControls();

        // Fix for error throwing when using non-english language
        $standard = trans('rainlab.builder::lang.form.control_group_standard');
        $widgets = trans('rainlab.builder::lang.form.control_group_widgets');
        $fieldTypes = array_merge(array_keys($controls[$standard]), array_keys($controls[$widgets]));
        $options = array_combine($fieldTypes, $fieldTypes);

        return [
            'toolbar' => false,
            'columns' => [
                'add' => [
                    'title' => 'rainlab.builder::lang.common.add',
                    'type' => 'checkbox',
                    'width' => '50px',
                ],
                'column' => [
                    'title' => 'rainlab.builder::lang.database.column_name_name',
                    'readOnly' => true,
                ],
                'label'  => [
                    'title' => 'rainlab.builder::lang.list.column_name_label',
                ],
                'type'   => [
                    'title' => 'rainlab.builder::lang.form.control_widget_type',
                    'type' => 'dropdown',
                    'options' => $options,
                ],
            ],
        ];
    }

    /**
     * getAddDatabaseFieldsDataTableValue returns the initial value for the DataTable widget that
     * is used in the "add database columns" popup.
     *
     * @param array $columns
     * @return array
     */
    protected function getAddDatabaseFieldsDataTableValue(array $columns)
    {
        // Map database column types to widget types.
        $typeMap = [
            'string' => 'text',
            'integer' => 'number',
            'text' => 'textarea',
            'timestamp' => 'datepicker',
            'smallInteger' => 'number',
            'bigInteger' => 'number',
            'date' => 'datepicker',
            'time' => 'datepicker',
            'dateTime' => 'datepicker',
            'binary' => 'checkbox',
            'boolean' => 'checkbox',
            'decimal' => 'number',
            'double' => 'number',
        ];

        return array_map(function($column) use ($typeMap) {
            return [
                'column' => $column['name'],
                'label' => str_replace('_', ' ', ucfirst($column['name'])),
                'type' => $typeMap[$column['type']] ?? $column['type'],
                'add' => false,
            ];
        }, $columns);
    }
}
