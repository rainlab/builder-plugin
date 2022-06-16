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
            'tabTitle' => Lang::get($widget->model->getPluginName()).'/'.Lang::get('rainlab.builder::lang.permission.tab'),
            'tabIcon' => 'icon-unlock-alt',
            'tabId' => $this->getTabId($pluginCode),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onPermissionsSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill(post());
        $model->save();

        Flash::success(Lang::get('rainlab.builder::lang.permission.saved'));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.Lang::get('rainlab.builder::lang.permission.tab'),
            'pluginCode' => $pluginCode
        ];

        return $result;
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
