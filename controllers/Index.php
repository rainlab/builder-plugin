<?php namespace RainLab\Builder\Controllers;

use Backend\Classes\Controller;
use Backend\Traits\InspectableContainer;
use RainLab\Builder\Widgets\PluginList;
use RainLab\Builder\Widgets\DatabaseTableList;
use RainLab\Builder\Widgets\ModelList;
use RainLab\Builder\Widgets\VersionList;
use RainLab\Builder\Widgets\LanguageList;
use RainLab\Builder\Widgets\ControllerList;
use BackendMenu;

/**
 * Index controller for Builder
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class Index extends Controller
{
    use InspectableContainer;

    public $implement = [
        \RainLab\Builder\Behaviors\IndexPluginOperations::class,
        \RainLab\Builder\Behaviors\IndexDatabaseTableOperations::class,
        \RainLab\Builder\Behaviors\IndexModelOperations::class,
        \RainLab\Builder\Behaviors\IndexModelFormOperations::class,
        \RainLab\Builder\Behaviors\IndexModelListOperations::class,
        \RainLab\Builder\Behaviors\IndexPermissionsOperations::class,
        \RainLab\Builder\Behaviors\IndexMenusOperations::class,
        \RainLab\Builder\Behaviors\IndexVersionsOperations::class,
        \RainLab\Builder\Behaviors\IndexLocalizationOperations::class,
        \RainLab\Builder\Behaviors\IndexControllerOperations::class,
        \RainLab\Builder\Behaviors\IndexDataRegistry::class
    ];

    public $requiredPermissions = ['rainlab.builder.manage_plugins'];

    /**
     * @var bool turboVisitControl
     */
    public $turboVisitControl = 'reload';

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('RainLab.Builder', 'builder', 'database');

        $this->bodyClass = 'compact-container sidenav-responsive';
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

        // Legacy styles for October v1.0
        if (!class_exists('System')) {
            $this->addCss('/plugins/rainlab/builder/assets/css/builder-v1.css', 'RainLab.Builder');
        }

        // The table widget scripts should be preloaded
        $this->addJs('/modules/backend/widgets/table/assets/js/build-min.js', 'core');
        $this->addJs('/plugins/rainlab/builder/assets/js/build-min.js', 'RainLab.Builder');

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
