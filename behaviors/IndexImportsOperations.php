<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Classes\ImportsModel;
use RainLab\Builder\Classes\PluginCode;
use Request;
use Flash;
use Lang;

/**
 * IndexImportsOperations functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexImportsOperations extends IndexOperationsBehaviorBase
{
    /**
     * @var string baseFormConfigFile
     */
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/classes/importsmodel/fields.yaml';

    /**
     * onImportsOpen
     */
    public function onImportsOpen()
    {
        $pluginCodeObj = $this->getPluginCode();

        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeBaseFormWidget($pluginCode);

        $result = [
            'tabTitle' => $widget->model->getPluginName().'/'.__("Import"),
            'tabIcon' => 'icon-arrow-circle-down',
            'tabId' => $this->getTabId($pluginCode),
            'tab' => $this->makePartial('tab', [
                'form' => $widget,
                'pluginCode' => $pluginCodeObj->toCode()
            ])
        ];

        return $result;
    }

    /**
     * onImportsSave
     */
    public function onImportsSave()
    {
        $pluginCodeObj = new PluginCode(Request::input('plugin_code'));

        $pluginCode = $pluginCodeObj->toCode();
        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill(post());
        $model->import();

        Flash::success(__("Import Complete"));

        $result['builderResponseData'] = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.__("Import"),
        ];

        return $result;
    }

    /**
     * getTabId
     */
    protected function getTabId($pluginCode)
    {
        return 'imports-'.$pluginCode;
    }

    /**
     * loadOrCreateBaseModel
     */
    protected function loadOrCreateBaseModel($pluginCode, $options = [])
    {
        $model = new ImportsModel;
        $model->loadPlugin($pluginCode);
        return $model;
    }
}
