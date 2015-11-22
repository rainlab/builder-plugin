<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use ApplicationException;
use SystemException;
use ValidationException;
use Exception;
use Lang;
use File;

/**
 * Manages plugin back-end menus.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class MenusModel extends PluginYamlModel
{
    public $menus = [];

    protected $yamlSection = 'menus';

    protected $pluginCodeObj;

    protected static $fillable = [
        'menus'
    ];

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        return $this->menus;
    }

    public function validate()
    {
        parent::validate();
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->permissions = $array;
    }

    public function setPluginCodeObj($pluginCodeObj)
    {
        $this->pluginCodeObj = $pluginCodeObj;
    }

    /**
     * Returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        if ($this->pluginCodeObj === null) {
            throw new SystemException('Error saving plugin menus model - the plugin code object is not set.');
        }

        return $this->pluginCodeObj->toPluginFilePath();
    }
}