<?php namespace RainLab\Builder\Models;

use Lang;
use File;
use Tailor\Classes\Blueprint\EntryBlueprint;
use Tailor\Classes\Blueprint\GlobalBlueprint;
use Tailor\Classes\Blueprint\SingleBlueprint;
use Tailor\Classes\Blueprint\StructureBlueprint;
use RainLab\Builder\Classes\BlueprintGenerator;
use RainLab\Builder\Classes\PluginVersion;
use Tailor\Classes\BlueprintIndexer;
use ApplicationException;

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
     * @var bool disableBlueprints when complete
     */
    public $disableBlueprints = false;

    /**
     * @var bool deleteBlueprintData when complete
     */
    public $deleteBlueprintData = false;

    /**
     * @var string pluginName
     */
    protected $pluginName;

    /**
     * @var object activeBlueprint
     */
    protected $activeBlueprint;

    /**
     * @var array activeConfig
     */
    protected $activeConfig;

    /**
     * @var array fillable attributes
     */
    protected static $fillable = [
        'blueprints',
        'disableBlueprints',
        'deleteBlueprintData',
    ];

    /**
     * fill
     */
    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (is_array($this->blueprints)) {
            foreach ($this->blueprints as &$configuration) {
                if (is_scalar($configuration)) {
                    $configuration = json_decode($configuration, true);
                }
            }
        }
    }

    /**
     * setBlueprintContext
     */
    public function setBlueprintContext($blueprint, $config)
    {
        $this->activeBlueprint = $blueprint;
        $this->activeConfig = $config;
    }

    /**
     * getBlueprintObject
     */
    public function getBlueprintObject()
    {
        return $this->activeBlueprint;
    }

    /**
     * getBlueprintConfig
     */
    public function getBlueprintConfig($name = null, $default = null)
    {
        if ($name === null) {
            return $this->activeConfig;
        }

        return array_key_exists($name, $this->activeConfig)
            ? $this->activeConfig[$name]
            : $default;
    }

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
        if (!$this->blueprints || !is_array($this->blueprints)) {
            throw new ApplicationException(__("There are no blueprints to import, please select a blueprint and try again."));
        }

        $generator = new BlueprintGenerator($this);
        $generator->generate();
    }

    /**
     * inspect a blueprint before import
     */
    public function inspect($blueprint): array
    {
        if (!$this->blueprints || !is_array($this->blueprints)) {
            return [];
        }

        $generator = new BlueprintGenerator($this);

        return $generator->inspect($blueprint);
    }

    /**
     * getBlueprintUuidOptions
     */
    public function getBlueprintUuidOptions()
    {
        $result = [];

        foreach (EntryBlueprint::listInProject() as $blueprint) {
            if (!isset($this->blueprints[$blueprint->uuid])) {
                $result[$blueprint->uuid] = $blueprint->handle;
            }
        }

        foreach (GlobalBlueprint::listInProject() as $blueprint) {
            if (!isset($this->blueprints[$blueprint->uuid])) {
                $result[$blueprint->uuid] = $blueprint->handle;
            }
        }

        asort($result);

        return $result;
    }

    /**
     * getPluginFilePath
     */
    public function getPluginFilePath($path)
    {
        $pluginDir = $this->getPluginCodeObj()->toPluginDirectoryPath();

        return File::symbolizePath("{$pluginDir}/{$path}");
    }

    /**
     * getPluginVersionInformation
     */
    public function getPluginVersionInformation()
    {
        $versionObj = new PluginVersion;

        return $versionObj->getPluginVersionInformation($this->getPluginCodeObj());
    }

    /**
     * getBlueprintFieldset
     */
    public function getBlueprintFieldset($blueprint = null)
    {
        $blueprint = $blueprint ?: $this->getBlueprintObject();

        $uuid = $blueprint->uuid ?? '???';

        $fieldset = BlueprintIndexer::instance()->findContentFieldset($uuid);
        if (!$fieldset) {
            throw new ApplicationException("Unable to find content fieldset definition with UUID of '{$uuid}'.");
        }

        return $fieldset;
    }

    /**
     * useListController
     */
    public function useListController(): bool
    {
        if (
            $this->activeBlueprint instanceof SingleBlueprint ||
            $this->activeBlueprint instanceof GlobalBlueprint
        ) {
            return false;
        }

        return true;
    }

    /**
     * useSettingModel
     */
    public function useSettingModel(): bool
    {
        if ($this->activeBlueprint instanceof GlobalBlueprint) {
            return true;
        }

        return false;
    }

    /**
     * wantsDatabaseMigration
     */
    public function wantsDatabaseMigration(): bool
    {
        if ($this->useSettingModel()) {
            return false;
        }

        return true;
    }

    /**
     * useMultisite
     */
    public function useMultisite()
    {
        return $this->activeBlueprint->useMultisite();
    }

    /**
     * useStructure
     */
    public function useStructure()
    {
        if ($this->activeBlueprint instanceof StructureBlueprint) {
            return true;
        }

        return false;
    }
}
