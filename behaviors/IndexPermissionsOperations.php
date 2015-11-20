<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\PermissionsModel;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin permissions management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexPermissionsOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/permissionsmodel/fields.yaml';

    public function onPermissionsOpen()
    {
        $pluginCodeObj = $this->getPluginCode();

        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeBaseFormWidget($pluginCode);

        $result = [
            'tabTitle' => $widget->model->getPluginName().'/'.Lang::get('rainlab.builder::lang.permission.tab'),
            'tabIcon' => 'icon-unlock-alt',
            'tabId' => $this->getTabId($pluginCodeObj->toCode()),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onPermissionsSave()
    {
        // $model = $this->loadPermissions();
        // $model->fill($_POST);
        // $model->save();

        // $result = $this->controller->widget->modelList->updateList();

        // Flash::success(Lang::get('rainlab.builder::lang.list.saved'));

        // $modelClass = Input::get('model_class');
        // $result['builderRepsonseData'] = [
        //     'builderObjectName' => $model->fileName,
        //     'tabId' => $this->getTabId($modelClass, $model->fileName),
        //     'tabTitle' => $model->getDisplayName(Lang::get('rainlab.builder::lang.list.tab_new_list'))
        // ];

        // return $result;
    }

    protected function loadPermissions()
    {
        return $this->loadOrCreateBaseModel(null, null);
    }

    protected function getTabId($pluginCode)
    {
        return 'permissions-'.$pluginCode;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new PermissionsModel();

        $model->loadPlugin($pluginCode);
        return $model;
    }
}