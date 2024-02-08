<?php namespace RainLab\Builder\Classes;

use Tailor\Classes\Blueprint\EntryBlueprint;
use Tailor\Classes\Blueprint\GlobalBlueprint;
use Tailor\Classes\BlueprintIndexer;
use Event;
use Lang;

/**
 * TailorBlueprintLibrary
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class TailorBlueprintLibrary
{
    use \October\Rain\Support\Traits\Singleton;

    const DEFAULT_DESIGN_TIME_PROVIDER = 'RainLab\Builder\Widgets\DefaultBlueprintDesignTimeProvider';

    /**
     * @var array blueprints registered
     */
    protected $blueprints = null;

    /**
     * @var array blueprintUuidCache for repeat lookups
     */
    protected $blueprintUuidCache = [];

    /**
     * getBlueprintInfo
     */
    public function getBlueprintInfo($blueprintUuid)
    {
        $blueprintObj = $this->getBlueprintObject($blueprintUuid);
        if (!$blueprintObj) {
            return null;
        }

        $blueprintClassName = get_class($blueprintObj);

        $blueprints = $this->listBlueprints();
        if (!array_key_exists($blueprintClassName, $blueprints)) {
            return null;
        }

        return [
            'blueprintObj' => $blueprintObj,
            'blueprintClass' => get_class($blueprintObj)
        ] + $blueprints[$blueprintClassName];
    }

    /**
     * getRelatedBlueprintUuids returns blueprints related to the supplied blueprint UUID
     */
    public function getRelatedBlueprintUuids($blueprintUuid)
    {
        $indexer = BlueprintIndexer::instance();
        $fieldset = $indexer->findContentFieldset($blueprintUuid);

        $relatedFieldTypes = ['entries'];

        $result = [];
        foreach ($fieldset->getAllFields() as $name => $field) {
            if (!in_array($field->type, $relatedFieldTypes)) {
                continue;
            }

            $bp = $this->getBlueprintObject($field->source, $field->source);
            if (!$bp) {
                continue;
            }

            $result[$name] = $bp->uuid;
        }

        return $result;
    }

    /**
     * registerBlueprint
     *
     * @param string $class Specifies the blueprint class name.
     * @param string $name Specifies the blueprint name, for example "Form blueprint".
     * @param string $description Specifies the blueprint description.
     * @param array $properties Specifies the blueprint properties.
     * The property definitions should be compatible with Inspector properties, similarly
     * to the Component properties: http://octobercms.com/docs/plugin/components#component-properties
     * @param string $designTimeProviderClass Specifies the blueprint design-time provider class name.
     * The class should extend RainLab\Builder\Classes\BlueprintDesignTimeProviderBase. If the class is not provided,
     * the default control design and design settings will be used.
     * The templates are used when a new controller is created. The templates should be specified as paths
     * to Twig files in the format ['~/plugins/author/plugin/blueprints/blueprintname/templates/view.htm.tpl'].
     */
    public function registerBlueprint($class, $name, $description, $properties, $designTimeProviderClass = null)
    {
        if (!$designTimeProviderClass) {
            $designTimeProviderClass = self::DEFAULT_DESIGN_TIME_PROVIDER;
        }

        $this->blueprints[$class] = [
            'class' => $class,
            'name' => Lang::get($name),
            'description' => Lang::get($description),
            'properties' => $properties,
            'designTimeProvider' => $designTimeProviderClass,
        ];
    }

    /**
     * listBlueprints
     */
    public function listBlueprints()
    {
        if ($this->blueprints !== null) {
            return $this->blueprints;
        }

        $this->blueprints = [];

        Event::fire('pages.builder.registerTailorBlueprints', [$this]);

        return $this->blueprints;
    }

    /**
     * getBlueprintObject
     */
    public function getBlueprintObject($uuid, $handle = null)
    {
        if (isset($this->blueprintUuidCache[$uuid])) {
            return $this->blueprintUuidCache[$uuid];
        }

        foreach (EntryBlueprint::listInProject() as $blueprint) {
            if ($blueprint->uuid === $uuid) {
                return $this->blueprintUuidCache[$blueprint->uuid] = $blueprint;
            }
            if ($handle && $blueprint->handle === $handle) {
                return $this->blueprintUuidCache[$blueprint->uuid] = $blueprint;
            }
        }

        foreach (GlobalBlueprint::listInProject() as $blueprint) {
            if ($blueprint->uuid === $uuid) {
                return $this->blueprintUuidCache[$blueprint->uuid] = $blueprint;
            }
            if ($handle && $blueprint->handle === $handle) {
                return $this->blueprintUuidCache[$blueprint->uuid] = $blueprint;
            }
        }
    }
}
