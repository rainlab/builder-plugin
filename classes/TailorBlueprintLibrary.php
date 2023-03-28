<?php namespace RainLab\Builder\Classes;

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

    protected $blueprints = null;

    /**
     * getBlueprintInfo
     */
    public function getBlueprintInfo($blueprintClassName, $blueprintHandle)
    {
        $blueprints = $this->listBlueprints();

        if (!array_key_exists($blueprintClassName, $blueprints)) {
            return null;
        }

        return $blueprints[$blueprintClassName];
    }

    /**
     * Registers a controller blueprint.
     * @param string $class Specifies the blueprint class name.
     * @param string $name Specifies the blueprint name, for example "Form blueprint".
     * @param string $description Specifies the blueprint description.
     * @param array $properties Specifies the blueprint properties.
     * The property definitions should be compatible with Inspector properties, similarly
     * to the Component properties: http://octobercms.com/docs/plugin/components#component-properties
     * @param string $configFilePropertyName Specifies the name of the controller property that contains the configuration file name for the blueprint.
     * @param string $designTimeProviderClass Specifies the blueprint design-time provider class name.
     * The class should extend RainLab\Builder\Classes\BlueprintDesignTimeProviderBase. If the class is not provided,
     * the default control design and design settings will be used.
     * @param string $configFileName Default blueprint configuration file name, for example config_form.yaml.
     * @param array $viewTemplates An array of view templates that are required for the blueprint.
     * The templates are used when a new controller is created. The templates should be specified as paths
     * to Twig files in the format ['~/plugins/author/plugin/blueprints/blueprintname/templates/view.htm.tpl'].
     */
    public function registerBlueprint($class, $name, $description, $properties, $configFilePropertyName, $designTimeProviderClass, $configFileName, $viewTemplates = [])
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
            'viewTemplates' => $viewTemplates,
            'configFileName' => $configFileName,
            'configPropertyName' => $configFilePropertyName
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
}
