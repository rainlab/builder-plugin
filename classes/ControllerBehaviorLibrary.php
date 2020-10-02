<?php namespace RainLab\Builder\Classes;

use Event;
use Lang;

/**
 * Manages Builder controller behavior library.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControllerBehaviorLibrary
{
    use \October\Rain\Support\Traits\Singleton;

    const DEFAULT_DESIGN_TIME_PROVIDER = 'RainLab\Builder\Widgets\DefaultBehaviorDesignTimeProvider';

    protected $behaviors = null;

    public function getBehaviorInfo($behaviorClassName)
    {
        $behaviors = $this->listBehaviors();

        if (!array_key_exists($behaviorClassName, $behaviors)) {
            return null;
        }

        return $behaviors[$behaviorClassName];
    }

    /**
     * Registers a controller behavior.
     * @param string $class Specifies the behavior class name.
     * @param string $name Specifies the behavior name, for example "Form behavior".
     * @param string $description Specifies the behavior description.
     * @param array $properties Specifies the behavior properties.
     * The property definitions should be compatible with Inspector properties, similarly
     * to the Component properties: http://octobercms.com/docs/plugin/components#component-properties
     * @param string $configFilePropertyName Specifies the name of the controller property that contains the configuration file name for the behavior.
     * @param string $designTimeProviderClass Specifies the behavior design-time provider class name.
     * The class should extend RainLab\Builder\Classes\BehaviorDesignTimeProviderBase. If the class is not provided,
     * the default control design and design settings will be used.
     * @param string $configFileName Default behavior configuration file name, for example config_form.yaml.
     * @param array $viewTemplates An array of view templates that are required for the behavior.
     * The templates are used when a new controller is created. The templates should be specified as paths
     * to Twig files in the format ['~/plugins/author/plugin/behaviors/behaviorname/templates/view.htm.tpl'].
     */
    public function registerBehavior($class, $name, $description, $properties, $configFilePropertyName, $designTimeProviderClass, $configFileName, $viewTemplates = [])
    {
        if (!$designTimeProviderClass) {
            $designTimeProviderClass = self::DEFAULT_DESIGN_TIME_PROVIDER;
        }

        $this->behaviors[$class] = [
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

    public function listBehaviors()
    {
        if ($this->behaviors !== null) {
            return $this->behaviors;
        }

        $this->behaviors = [];

        /**
         * @event pages.builder.registerControllerBehaviors
         * Provides a hook to register controller behaviors that should be made available to the RainLab.Builder plugin
         *
         * Example usage:
         *
         *     Event::listen('pages.builder.registerControllerBehaviors', function ((ControllerBehaviorLibrary) $library) {
         *           $library->registerBehavior(
         *               'Backend\Behaviors\FormController', // Class name of the behavior
         *               'rainlab.builder::lang.controller.behavior_form_controller', // Name in the UI
         *               'rainlab.builder::lang.controller.behavior_form_controller_description', // Description in the UI
         *               $properties, // Property definitions for configuring the behavior, must be compatible with Inspector properties (see https://octobercms.com/docs/plugin/components#component-properties)
         *               'formConfig', // Property name for the config property on the controller implementing the behavior
         *               null, // Design time provider class extending RainLab\Builder\Classes\BehaviorDesignTimeProviderBase
         *               'config_form.yaml', // Default behavior configuration file name
         *               $templates // Array of view templates required for the behavior, used when a new controller is created.
         *           );
         *     });
         *
         */
        Event::fire('pages.builder.registerControllerBehaviors', [$this]);

        return $this->behaviors;
    }
}
