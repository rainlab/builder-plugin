<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\MenusModel;
use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin back-end menu management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexMenusOperations extends IndexOperationsBehaviorBase
{
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/menusmodel/fields.yaml';

    public function onMenusOpen()
    {
        $pluginCodeObj = $this->getPluginCode();

        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeBaseFormWidget($pluginCode);

        $result = [
            'tabTitle' => $widget->model->getPluginName().'/'.Lang::get('rainlab.builder::lang.menu.tab'),
            'tabIcon' => 'icon-location-arrow',
            'tabId' => $this->getTabId($pluginCode),
            'tab' => $this->makePartial('tab', [
                'form'  => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    public function onMenusSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill($_POST);
        $model->save();

        Flash::success(Lang::get('rainlab.builder::lang.menu.saved'));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.Lang::get('rainlab.builder::lang.menu.tab'),
        ];

        return $result;
    }

    protected function getTabId($pluginCode)
    {
        return 'menus-'.$pluginCode;
    }

    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new MenusModel();

        $model->loadPlugin($pluginCode);
        return $model;
    }
}
