<?php namespace RainLab\Builder\Classes;

use System\Classes\PluginBase;

/**
 * Holds a plugin code object and a plugin information class instancd.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginVector
{
    /**
     * @var PluginBase Plugin information class instance
     */
    public $plugin;

    /**
     * @var PluginCode Plugin code object
     */
    public $pluginCodeObj;

    public function __construct(PluginBase $plugin, PluginCode $pluginCodeObj)
    {
        $this->plugin = $plugin;
        $this->pluginCodeObj = $pluginCodeObj;
    }

    public function getPluginName()
    {
        if (!$this->plugin) {
            return null;
        }

        $pluginInfo = $this->plugin->pluginDetails();
        if (!isset($pluginInfo['name'])) {
            return null;
        }

        return $pluginInfo['name'];
    }
}