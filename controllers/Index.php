<?php namespace RainLab\Builder\Controllers;

use Backend\Classes\Controller;
use Backend\Traits\InspectableContainer;
use RainLab\Builder\Widgets\PluginList;
use RainLab\Builder\Widgets\DatabaseTableList;
use RainLab\Builder\Widgets\ModelList;
use RainLab\Builder\Traits\IndexPluginOperations;
use ApplicationException;
use Exception;
use BackendMenu;

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
        'RainLab.Builder.Behaviors.IndexMenusOperations'
    ];

    public $requiredPermissions = ['rainlab.buileder.*'];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('RainLab.Builder', 'builder', 'database');

        $this->bodyClass = 'compact-container side-panel-not-fixed';
        $this->pageTitle = 'rainlab.builder::lang.plugin.name';

        new PluginList($this, 'pluginList');
        new DatabaseTableList($this, 'databaseTabelList');
        new ModelList($this, 'modelList');
    }

    public function index()
    {
        $this->addCss('/plugins/rainlab/builder/assets/css/builder.css', 'RainLab.Builder');

        // TODO: combine the scripts
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.base.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.plugin.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.databasetable.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.model.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.modelform.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.modellist.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.permission.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.entity.menus.js', 'RainLab.Builder');
        $this->addJs('/plugins/rainlab/builder/assets/js/builder.index.js', 'RainLab.Builder');

        $this->pageTitleTemplate = '%s Builder';
    }

    public function setBuilderActivePlugin($pluginCpde, $refreshPluginList = false)
    {
        $this->widget->pluginList->setActivePlugin($pluginCpde);

        $result = [];
        if ($refreshPluginList) {
            $result = $this->widget->pluginList->updateList();
        }

        $result = array_merge(
            $result,
            $this->widget->databaseTabelList->refreshActivePlugin(),
            $this->widget->modelList->refreshActivePlugin()
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