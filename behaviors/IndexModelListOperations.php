<?php namespace RainLab\Builder\Behaviors;

use Str;
use Lang;
use Input;
use Flash;
use Request;
use System\Classes\PluginManager;
use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\ModelListModel;
use RainLab\Builder\Models\ModelModel;

/**
 * IndexModelListOperations provides model list management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexModelListOperations extends IndexOperationsBehaviorBase
{
    /**
     * @var string baseFormConfigFile
     */
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/modellistmodel/fields.yaml';

    /**
     * extendBaseFormWidgetConfig
     */
    protected function extendBaseFormWidgetConfig($config)
    {
        $typeOptions = array_get($config->tabs, 'fields.columns.columns.type.options');

        $pluginColumns = PluginManager::instance()->getRegistrationMethodValues('registerListColumnTypes');
        foreach ($pluginColumns as $customColumns) {
            foreach (array_keys($customColumns) as $customColumn) {
                $typeOptions[$customColumn] = __(Str::studly($customColumn));
            }
        }

        array_set($config->tabs, 'fields.columns.columns.type.options', $typeOptions);
        return $config;
    }

    /**
     * onModelListCreateOrOpen
     */
    public function onModelListCreateOrOpen()
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
            'tabTitle' => $widget->model->getDisplayName(Lang::get('rainlab.builder::lang.list.tab_new_list')),
            'tabIcon' => 'icon-list',
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
     * onModelListSave
     */
    public function onModelListSave()
    {
        $model = $this->loadOrCreateListFromPost();
        $model->fill(post());
        $model->save();

        $result = $this->controller->widget->modelList->updateList();

        Flash::success(Lang::get('rainlab.builder::lang.list.saved'));

        $modelClass = Input::get('model_class');
        $result['builderResponseData'] = [
            'builderObjectName' => $model->fileName,
            'tabId' => $this->getTabId($modelClass, $model->fileName),
            'tabTitle' => $model->getDisplayName(Lang::get('rainlab.builder::lang.list.tab_new_list'))
        ];

        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    /**
     * onModelListDelete
     */
    public function onModelListDelete()
    {
        $model = $this->loadOrCreateListFromPost();

        $model->deleteModel();

        $result = $this->controller->widget->modelList->updateList();

        $modelClass = Input::get('model_class');
        $this->mergeRegistryDataIntoResult($result, $model, $modelClass);

        return $result;
    }

    /**
     * onModelListGetModelFields
     */
    public function onModelListGetModelFields()
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

    /**
     * onModelListLoadDatabaseColumns
     */
    public function onModelListLoadDatabaseColumns()
    {
        $columns = ModelModel::getModelColumnsAndTypes($this->getPluginCode(), Input::get('model_class'));

        return [
            'responseData' => [
                'columns' => $columns
            ]
        ];
    }

    /**
     * loadOrCreateListFromPost
     */
    protected function loadOrCreateListFromPost()
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

        return 'modelList-'.$modelClass.'-'.$fileName;
    }

    /**
     * loadOrCreateBaseModel
     */
    protected function loadOrCreateBaseModel($fileName, $options = [])
    {
        $model = new ModelListModel();

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
            'lists' => ModelListModel::getPluginRegistryData($pluginCode, $modelClass),
            'pluginCode' => $pluginCode,
            'modelClass' => $fullClassName
        ];
    }
}
