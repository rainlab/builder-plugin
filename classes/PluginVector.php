<?php namespace RainLab\Builder\Classes;

use System\Classes\PluginBase;
use System\Classes\PluginManager;

/**
 * PluginVector holds a plugin code object and a plugin information class instance.
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

    /**
     * __construct
     */
    public function __construct(PluginBase $plugin, PluginCode $pluginCodeObj)
    {
        $this->plugin = $plugin;
        $this->pluginCodeObj = $pluginCodeObj;
    }

    /**
     * createFromPluginCode
     */
    public static function createFromPluginCode($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $plugins = PluginManager::instance()->getPlugins();

        foreach ($plugins as $code => $plugin) {
            if ($code == $pluginCode) {
                return new PluginVector($plugin, $pluginCodeObj);
            }
        }

        return null;
    }

    /**
     * getPluginName
     */
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
