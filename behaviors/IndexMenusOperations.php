<?php namespace RainLab\Builder\Behaviors;

use BackendMenu;
use System\Classes\PluginManager;
use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\MenusModel;
use RainLab\Builder\Classes\PluginCode;
use Throwable;
use Request;
use Flash;
use Lang;

/**
 * IndexMenusOperations is plugin backend menu management functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexMenusOperations extends IndexOperationsBehaviorBase
{
    /**
     * @var string baseFormConfigFile
     */
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/menusmodel/fields.yaml';

    /**
     * onMenusOpen
     */
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

    /**
     * onMenusSave
     */
    public function onMenusSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill(post());
        $model->save();

        Flash::success(Lang::get('rainlab.builder::lang.menu.saved'));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.Lang::get('rainlab.builder::lang.menu.tab'),
        ];

        // Feature is nice to have, only supported in >3.3.9
        try {
            PluginManager::instance()->reloadPlugins();
            BackendMenu::resetCache();

            $result['mainMenu'] = $this->controller->makeLayoutPartial('mainmenu');
            $result['mainMenuLeft'] = $this->controller->makeLayoutPartial('mainmenu', ['isVerticalMenu'=>true]);
        }
        catch (Throwable $ex) {}

        return $result;
    }

    /**
     * getTabId
     */
    protected function getTabId($pluginCode)
    {
        return 'menus-'.$pluginCode;
    }

    /**
     * loadOrCreateBaseModel
     */
    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new MenusModel();

        $model->loadPlugin($pluginCode);

        return $model;
    }
}
