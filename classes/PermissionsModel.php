<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use ApplicationException;
use SystemException;
use Exception;
use Lang;
use File;

/**
 * Manages plugin permissions information.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PermissionsModel extends PluginYamlModel
{
    public $permissions = [];

    protected $yamlSection = 'permissions';

    protected static $fillable = [
        'permissions'
    ];

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        return $this->permissions;
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->permissions = $array;
    }
}