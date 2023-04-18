<?php namespace RainLab\Builder\Models;

use RainLab\Builder\Classes\PluginCode;
use ApplicationException;
use Lang;
use File;

/**
 * PluginYamlModel is a base class for models that keep data in the plugin.yaml file.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class PluginYamlModel extends YamlModel
{
    /**
     * @var string pluginName
     */
    protected $pluginName;

    /**
     * loadPlugin
     */
    public function loadPlugin($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $filePath = self::pluginSettingsFileExists($pluginCodeObj);
        if ($filePath === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.plugin.error_settings_not_editable'));
        }

        $this->initPropertiesFromPluginCodeObject($pluginCodeObj);

        $result = parent::load($filePath);

        $this->loadCommonProperties();

        return $result;
    }

    /**
     * getPluginName
     */
    public function getPluginName()
    {
        return Lang::get($this->pluginName);
    }

    /**
     * loadCommonProperties
     */
    protected function loadCommonProperties()
    {
        if (!array_key_exists('plugin', $this->originalFileData)) {
            return;
        }

        $pluginData = $this->originalFileData['plugin'];

        if (array_key_exists('name', $pluginData)) {
            $this->pluginName = $pluginData['name'];
        }
    }

    /**
     * initPropertiesFromPluginCodeObject
     */
    protected function initPropertiesFromPluginCodeObject($pluginCodeObj)
    {
    }

    /**
     * pluginSettingsFileExists
     */
    protected static function pluginSettingsFileExists($pluginCodeObj)
    {
        $filePath = File::symbolizePath($pluginCodeObj->toPluginFilePath());
        if (File::isFile($filePath)) {
            return $filePath;
        }

        return false;
    }
}
