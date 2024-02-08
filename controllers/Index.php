<?php namespace RainLab\Builder\Controllers;

use Request;
use Backend\Classes\Controller;
use RainLab\Builder\Widgets\PluginList;
use RainLab\Builder\Widgets\DatabaseTableList;
use RainLab\Builder\Widgets\ModelList;
use RainLab\Builder\Widgets\VersionList;
use RainLab\Builder\Widgets\LanguageList;
use RainLab\Builder\Widgets\ControllerList;
use RainLab\Builder\Widgets\CodeList;
use BackendMenu;

/**
 * Index controller for Builder
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class Index extends Controller
{
    use \Backend\Traits\InspectableContainer;

    /**
     * @var array implement
     */
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
        \RainLab\Builder\Behaviors\IndexCodeOperations::class,
        \RainLab\Builder\Behaviors\IndexControllerOperations::class,
        \RainLab\Builder\Behaviors\IndexImportsOperations::class,
        \RainLab\Builder\Behaviors\IndexDataRegistry::class
    ];

    /**
     * @var array requiredPermissions
     */
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
        $this->pageTitle = "Builder";
    }

    /**
     * beforeDisplay
     */
    public function beforeDisplay()
    {
        new PluginList($this, 'pluginList');
        new DatabaseTableList($this, 'databaseTableList');
        new ModelList($this, 'modelList');
        new VersionList($this, 'versionList');
        new LanguageList($this, 'languageList');
        new ControllerList($this, 'controllerList');
        new CodeList($this, 'codeList');

        $this->bindFormWidgetToController();
    }

    /**
     * bindFormWidgetToController
     */
    protected function bindFormWidgetToController()
    {
        if (!Request::ajax() || !post('operationClass') || !post('formWidgetAlias')) {
            return;
        }

        $extension = $this->asExtension(post('operationClass'));
        if (!$extension) {
            return;
        }

        $extension->bindFormWidgetToController(post('formWidgetAlias'));
    }

    /**
     * index
     */
    public function index()
    {
        $this->addCss('/plugins/rainlab/builder/assets/css/builder.css', 'RainLab.Builder');

        // The table widget scripts should be preloaded
        $this->addJs('/modules/backend/widgets/table/assets/js/build-min.js', 'core');
        $this->addJs('/plugins/rainlab/builder/assets/js/build-min.js', 'RainLab.Builder');

        $this->pageTitleTemplate = '%s Builder';
    }

    /**
     * setBuilderActivePlugin
     */
    public function setBuilderActivePlugin($pluginCode, $refreshPluginList = false)
    {
        $this->widget->pluginList->setActivePlugin($pluginCode);

        $result = [];
        if ($refreshPluginList) {
            $result = $this->widget->pluginList->updateList();
        }

        $result = array_merge(
            $result,
            $this->widget->databaseTableList->refreshActivePlugin(),
            $this->widget->modelList->refreshActivePlugin(),
            $this->widget->versionList->refreshActivePlugin(),
            $this->widget->languageList->refreshActivePlugin(),
            $this->widget->controllerList->refreshActivePlugin(),
            $this->widget->codeList->refreshActivePlugin()
        );

        return $result;
    }

    /**
     * getBuilderActivePluginVector
     */
    public function getBuilderActivePluginVector()
    {
        return $this->widget->pluginList->getActivePluginVector();
    }

    /**
     * updatePluginList
     */
    public function updatePluginList()
    {
        return $this->widget->pluginList->updateList();
    }
}
