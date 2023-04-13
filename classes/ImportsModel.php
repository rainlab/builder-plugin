<?php namespace RainLab\Builder\Classes;

use Str;
use Lang;
use Tailor\Classes\Blueprint\GlobalBlueprint;
use Tailor\Classes\Blueprint\EntryBlueprint;
use Tailor\Classes\BlueprintIndexer;
use SystemException;

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
     * @var array selectedBlueprints are the blueprint handles already selected
     */
    public $selectedBlueprints = [];

    /**
     * @var \Tailor\Classes\Blueprint loadedBlueprint
     */
    public $loadedBlueprint;

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
     * setSelectedBlueprints
     */
    public function setSelectedBlueprints($blueprints)
    {
        $this->selectedBlueprints = $blueprints;
    }

    /**
     * getBlueprintUuidOptions
     */
    public function getBlueprintUuidOptions()
    {
        $result = [];

        foreach (EntryBlueprint::listInProject() as $blueprint) {
            if (!in_array($blueprint->uuid, $this->selectedBlueprints)) {
                $blueprintClass = get_class($blueprint);
                $result["{$blueprint->uuid}@{$blueprintClass}"] = $blueprint->handle;
            }
        }

        foreach (GlobalBlueprint::listInProject() as $blueprint) {
            if (!in_array($blueprint->uuid, $this->selectedBlueprints)) {
                $blueprintClass = get_class($blueprint);
                $result["{$blueprint->uuid}@{$blueprintClass}"] = $blueprint->handle;
            }
        }

        return $result;
    }

    /**
     * loadBlueprintInfo
     */
    public function loadBlueprintInfo($class, $uuid)
    {
        switch ($class) {
            case \Tailor\Classes\Blueprint\EntryBlueprint::class:
            case \Tailor\Classes\Blueprint\SingleBlueprint::class:
            case \Tailor\Classes\Blueprint\StreamBlueprint::class:
            case \Tailor\Classes\Blueprint\GlobalBlueprint::class:
                $indexerMethod = 'findSection';
                break;

            case \Tailor\Classes\Blueprint\StructureBlueprint::class:
                $indexerMethod = 'findGlobal';
                break;

            default:
                return;
        }

        $blueprint = BlueprintIndexer::instance()->$indexerMethod($uuid);

        $this->loadedBlueprint = $blueprint;
    }

    /**
     * generateBlueprintConfiguration
     */
    public function generateBlueprintConfiguration(): array
    {
        $blueprint = $this->loadedBlueprint;
        if (!$blueprint) {
            throw new SystemException(sprintf('An active blueprint is not set in the %s object.', get_class($this)));
        }

        $handleBase = class_basename($blueprint->handle);

        return [
            'name' => $blueprint->name,
            'controllerClass' => Str::plural($handleBase),
            'modelClass' => Str::singular($handleBase),
        ];
    }
}
