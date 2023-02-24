<?php namespace RainLab\Builder\Classes;

use Tailor\Classes\Blueprint\GlobalBlueprint;
use Tailor\Classes\Blueprint\EntryBlueprint;
use Lang;

/**
 * ImportsModel manages plugin blueprint imports
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ImportsModel extends BaseModel
{
    /**
     * @var array blueprints
     */
    public $blueprints = [];

    /**
     * @var string pluginName
     */
    protected $pluginName;

    /**
     * loadPlugin
     */
    public function loadPlugin($pluginCode)
    {
        $this->pluginName = $pluginCode;
    }

    /**
     * getPluginName
     */
    public function getPluginName()
    {
        return Lang::get($this->pluginName);
    }

    /**
     * import runs the import
     */
    public function import()
    {
    }

    /**
     * getBlueprintUuidOptions
     */
    public function getBlueprintUuidOptions()
    {
        $result = [];

        foreach (EntryBlueprint::listInProject() as $blueprint) {
            $result[$blueprint->uuid] = $blueprint->handle;
        }

        foreach (GlobalBlueprint::listInProject() as $blueprint) {
            $result[$blueprint->uuid] = $blueprint->handle;
        }

        return $result;
    }
}
