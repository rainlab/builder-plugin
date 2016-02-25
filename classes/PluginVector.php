<?php namespace RainLab\Builder\Classes;

use System\Classes\PluginBase;
use System\Classes\PluginManager;

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

    public static function createFromPluginCode($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $plugins = PluginManager::instance()->getPlugins();

        foreach ($plugins as $code=>$plugin) {
            if ($code == $pluginCode) {
                return new PluginVector($plugin, $pluginCodeObj);
            }
        }

        return null;
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