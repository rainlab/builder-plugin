<?php namespace RainLab\Builder\Behaviors;

use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\ImportsModel;
use RainLab\Builder\Classes\PluginCode;
use System\Classes\VersionManager;
use Flash;

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
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/importsmodel/fields.yaml';

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
        $pluginCodeObj = new PluginCode(post('plugin_code'));
        $pluginCode = $pluginCodeObj->toCode();

        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill(post());
        $model->import();

        Flash::success(__("Import Complete"));

        $builderResponseData = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.__("Import"),
        ];

        // Refresh everything
        $result = $this->controller->setBuilderActivePlugin($pluginCode);
        $result['builderResponseData'] = $builderResponseData;

        return $result;
    }

    /**
     * onMigrateDatabase
     */
    public function onMigrateDatabase()
    {
        $pluginCodeObj = new PluginCode(post('plugin_code'));

        VersionManager::instance()->updatePlugin($pluginCodeObj->toCode());

        Flash::success(__("Migration Complete"));
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
