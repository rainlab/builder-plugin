<?php namespace RainLab\Builder\Widgets;

use Str;
use File;
use Input;
use Backend\Classes\WidgetBase;
use System\Classes\PluginManager;
use RainLab\Builder\Classes\PluginCode;
use RainLab\Builder\Models\Settings as PluginSettings;
use RainLab\Builder\Classes\PluginVector;
use Exception;

/**
 * Plugin list widget.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginList extends WidgetBase
{
    use \Backend\Traits\SearchableWidget;

    /**
     * @var string noRecordsMessage
     */
    public $noRecordsMessage = 'rainlab.builder::lang.plugin.no_records';

    /**
     * __construct
     */
    public function __construct($controller, $alias)
    {
        $this->alias = $alias;

        parent::__construct($controller, []);
        $this->bindToController();
    }

    /**
     * render the widget.
     * @return string
     */
    public function render()
    {
        return $this->makePartial('body', $this->getRenderData());
    }

    /**
     * setActivePlugin
     */
    public function setActivePlugin($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $this->putSession('activePlugin', $pluginCodeObj->toCode());
    }

    /**
     * getActivePluginVector
     */
    public function getActivePluginVector()
    {
        $pluginCode = $this->getActivePluginCode();

        try {
            if (strlen($pluginCode)) {
                $pluginCodeObj = new PluginCode($pluginCode);
                $path = $pluginCodeObj->toPluginInformationFilePath();
                if (!File::isFile(File::symbolizePath($path))) {
                    return null;
                }

                $plugins = PluginManager::instance()->getPlugins();
                foreach ($plugins as $code => $plugin) {
                    if ($code == $pluginCode) {
                        return new PluginVector($plugin, $pluginCodeObj);
                    }
                }
            }
        }
        catch (Exception $ex) {
            return null;
        }

        return null;
    }

    /**
     * updateList
     */
    public function updateList()
    {
        return ['#'.$this->getId('plugin-list') => $this->makePartial('items', $this->getRenderData())];
    }

    /**
     * onUpdate
     */
    public function onUpdate()
    {
        return $this->updateList();
    }

    /**
     * onSearch
     */
    public function onSearch()
    {
        $this->setSearchTerm(Input::get('search'));
        return $this->updateList();
    }

    /**
     * onToggleFilter
     */
    public function onToggleFilter()
    {
        $mode = $this->getFilterMode();
        $this->setFilterMode($mode == 'my' ? 'all' : 'my');

        $result = $this->updateList();
        $result['#'.$this->getId('toolbar-buttons')] = $this->makePartial('toolbar-buttons');

        return $result;
    }

    /**
     * getData
     */
    protected function getData()
    {
        $plugins = $this->getPluginList();
        $searchTerm = Str::lower($this->getSearchTerm());

        // Apply the search
        //
        if (strlen($searchTerm)) {
            $words = explode(' ', $searchTerm);
            $result = [];

            foreach ($plugins as $code => $plugin) {
                if ($this->textMatchesSearch($words, $plugin['full-text'])) {
                    $result[$code] = $plugin;
                }
            }

            $plugins = $result;
        }

        // Apply the my plugins / all plugins filter
        //
        $mode = $this->getFilterMode();
        if ($mode == 'my') {
            $namespace = PluginSettings::instance()->author_namespace;

            $result = [];
            foreach ($plugins as $code => $plugin) {
                if (strcasecmp($plugin['namespace'], $namespace) === 0) {
                    $result[$code] = $plugin;
                }
            }

            $plugins = $result;
        }

        return $plugins;
    }

    /**
     * getPluginList
     */
    protected function getPluginList()
    {
        $plugins = PluginManager::instance()->getPlugins();

        $result = [];
        foreach ($plugins as $code => $plugin) {
            $pluginInfo = $plugin->pluginDetails();

            $itemInfo = [
                'name' => isset($pluginInfo['name']) ? $pluginInfo['name'] : 'rainlab.builder::lang.plugin.no_name',
                'description' => isset($pluginInfo['description']) ? $pluginInfo['description'] : 'rainlab.builder::lang.plugin.no_description',
                'icon' => isset($pluginInfo['icon']) ? $pluginInfo['icon'] : null
            ];

            list($namespace) = explode('\\', get_class($plugin));
            $itemInfo['namespace'] = trim($namespace);
            $itemInfo['full-text'] = trans($itemInfo['name']).' '.trans($itemInfo['description']);

            $result[$code] = $itemInfo;
        }

        uasort($result, function($a, $b) {
            return strcmp(trans($a['name']), trans($b['name']));
        });

        return $result;
    }

    /**
     * setFilterMode
     */
    protected function setFilterMode($mode)
    {
        $this->putSession('filter', $mode);
    }

    /**
     * getFilterMode
     */
    protected function getFilterMode()
    {
        return $this->getSession('filter', 'my');
    }

    /**
     * getActivePluginCode
     */
    protected function getActivePluginCode()
    {
        return $this->getSession('activePlugin');
    }

    /**
     * getRenderData
     */
    protected function getRenderData()
    {
        return [
            'items'=>$this->getData()
        ];
    }
}
