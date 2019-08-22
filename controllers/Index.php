<?php namespace RainLab\Builder\Controllers;

use Backend\Classes\Controller;
use Backend\Traits\InspectableContainer;
use RainLab\Builder\Widgets\PluginList;
use RainLab\Builder\Widgets\DatabaseTableList;
use RainLab\Builder\Widgets\ModelList;
use RainLab\Builder\Widgets\VersionList;
use RainLab\Builder\Widgets\LanguageList;
use RainLab\Builder\Widgets\ControllerList;
use Backend;
use BackendMenu;
use Config;

/**
 * Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class Index extends Controller
{
    use InspectableContainer;

    public $implement = [
        'RainLab.Builder.Behaviors.IndexPluginOperations',
        'RainLab.Builder.Behaviors.IndexDatabaseTableOperations',
        'RainLab.Builder.Behaviors.IndexModelOperations',
        'RainLab.Builder.Behaviors.IndexModelFormOperations',
        'RainLab.Builder.Behaviors.IndexModelListOperations',
        'RainLab.Builder.Behaviors.IndexPermissionsOperations',
        'RainLab.Builder.Behaviors.IndexMenusOperations',
        'RainLab.Builder.Behaviors.IndexVersionsOperations',
        'RainLab.Builder.Behaviors.IndexLocalizationOperations',
        'RainLab.Builder.Behaviors.IndexControllerOperations',
        'RainLab.Builder.Behaviors.IndexDataRegistry'
    ];

    public $requiredPermissions = ['rainlab.builder.manage_plugins'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('RainLab.Builder', 'builder', 'database');

        $this->bodyClass = 'compact-container';
        $this->pageTitle = 'rainlab.builder::lang.plugin.name';

        new PluginList($this, 'pluginList');
        new DatabaseTableList($this, 'databaseTabelList');
        new ModelList($this, 'modelList');
        new VersionList($this, 'versionList');
        new LanguageList($this, 'languageList');
        new ControllerList($this, 'controllerList');
    }

    public function index()
    {
        $this->addCss('/plugins/rainlab/builder/assets/css/builder.css', 'RainLab.Builder');

        // The table widget scripts should be preloaded
        $this->addJs('/modules/backend/widgets/table/assets/js/build-min.js', 'core');

        if (Config::get('develop.decompileBackendAssets', false)) {
            // Allow decompiled backend assets for RainLab Builder
            $assets = Backend::decompileAsset('../../plugins/rainlab/builder/assets/js/build.js', true);

            foreach ($assets as $asset) {
                $this->addJs($asset, 'RainLab.Builder');
            }
        } else {
            $this->addJs('/plugins/rainlab/builder/assets/js/build-min.js', 'RainLab.Builder');
        }

        $this->pageTitleTemplate = '%s Builder';
    }

    public function setBuilderActivePlugin($pluginCode, $refreshPluginList = false)
    {
        $this->widget->pluginList->setActivePlugin($pluginCode);

        $result = [];
        if ($refreshPluginList) {
            $result = $this->widget->pluginList->updateList();
        }

        $result = array_merge(
            $result,
            $this->widget->databaseTabelList->refreshActivePlugin(),
            $this->widget->modelList->refreshActivePlugin(),
            $this->widget->versionList->refreshActivePlugin(),
            $this->widget->languageList->refreshActivePlugin(),
            $this->widget->controllerList->refreshActivePlugin()
        );

        return $result;
    }

    public function getBuilderActivePluginVector()
    {
        return $this->widget->pluginList->getActivePluginVector();
    }

    public function updatePluginList()
    {
        return $this->widget->pluginList->updateList();
    }
}
