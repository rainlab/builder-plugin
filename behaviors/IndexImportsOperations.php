<?php namespace RainLab\Builder\Behaviors;

use BackendMenu;
use RainLab\Builder\Classes\IndexOperationsBehaviorBase;
use RainLab\Builder\Models\ImportsModel;
use RainLab\Builder\Classes\PluginCode;
use System\Classes\PluginManager;
use System\Helpers\Cache as CacheHelper;
use System\Classes\VersionManager;
use ApplicationException;
use Throwable;
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
    protected $baseFormConfigFile = '~/plugins/rainlab/builder/models/importsmodel/fields.yaml';

    /**
     * @var string selectFormConfigFile
     */
    protected $selectFormConfigFile = '~/plugins/rainlab/builder/models/importsmodel/fields.yaml';

    /**
     * onImportsOpen
     */
    public function onImportsOpen()
    {
        $pluginCodeObj = $this->getPluginCode();
        $pluginCode = $pluginCodeObj->toCode();
        $widget = $this->makeSelectionFormWidget($pluginCode);

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
    public function onImportsShowConfirmPopup()
    {
        if (!post('blueprints')) {
            throw new ApplicationException(__("There are no blueprints to import, please select a blueprint and try again."));
        }

        $pluginCodeObj = $this->getPluginCode();

        $options = [
            'pluginCode' => $pluginCodeObj->toCode()
        ];

        $this->baseFormConfigFile = '~/plugins/rainlab/builder/models/importsmodel/fields_import.yaml';
        $widget = $this->makeBaseFormWidget(null, $options);

        return $this->makePartial('import-blueprints-popup-form', [
            'form' => $widget,
            'pluginCode' => $pluginCodeObj->toCode()
        ]);
    }

    /**
     * onImportsSave
     */
    public function onImportsSave()
    {
        if (post('delete_blueprint_data')) {
            $confirmText = trim(strtolower(post('delete_blueprint_data_confirm')));
            if ($confirmText !== 'ok') {
                throw new ApplicationException(__("Type OK in the field to confirm you want to destroy the existing blueprint data."));
            }
        }

        $pluginCodeObj = new PluginCode(post('plugin_code'));
        $pluginCode = $pluginCodeObj->toCode();

        // Validate plugin code matches
        $vectorCode = $this->controller->getBuilderActivePluginVector()->pluginCodeObj->toCode();
        if ($pluginCode !== $vectorCode) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.common.not_match'));
        }

        $model = $this->loadOrCreateBaseModel($pluginCodeObj->toCode());

        // Disable blueprints when finished
        if (post('disable_blueprints')) {
            $model->disableBlueprints = true;
        }

        // Disable blueprints when finished
        if (post('delete_blueprint_data')) {
            $model->deleteBlueprintData = true;
        }

        // Perform import
        $model->setPluginCodeObj($pluginCodeObj);
        $model->fill(post());
        $model->import();

        // Migrate database
        if (post('migrate_database')) {
            VersionManager::instance()->updatePlugin($pluginCode);
        }

        CacheHelper::instance()->clearBlueprintCache();

        Flash::success(__("Import Complete"));

        $builderResponseData = [
            'tabId' => $this->getTabId($pluginCode),
            'tabTitle' => $model->getPluginName().'/'.__("Import"),
        ];

        // Refresh everything
        $result = $this->controller->setBuilderActivePlugin($pluginCode);
        $result['builderResponseData'] = $builderResponseData;

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

    /**
     * makeBaseFormWidget
     */
    protected function makeSelectionFormWidget($modelCode, $options = [])
    {
        if (!strlen($this->selectFormConfigFile)) {
            throw new ApplicationException(sprintf('Base form configuration file is not specified for %s behavior', get_class($this)));
        }

        $widgetConfig = $this->makeConfig($this->selectFormConfigFile);
        $widgetConfig->model = $this->loadOrCreateBaseModel($modelCode, $options);

        return $this->makeWidget(\Backend\Widgets\Form::class, $widgetConfig);
    }
}
