<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use Exception;
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

    public function getBehaviorInformation($behaviorClassName)
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
     * @param array $properties Specifies the behavior properties.
     * The property definitions should be compatible with Inspector properties, similarly
     * to the Component properties: http://octobercms.com/docs/plugin/components#component-properties
     * @param $configFilePropertyName Specifies the name of the controller property that contains the configuration file name for the behavior.
     * @param string $designTimeProviderClass Specifies the behavior design-time provider class name.
     * The class should extend RainLab\Builder\Classes\BehaviorDesignTimeProviderBase. If the class is not provided,
     * the default control design and design settings will be used.
     * @param $viewTemplates An array of view templates that are required for the behavior.
     * The templates are used when a new controller is created.
     */
    public function registerBehavior($class, $name, $properties, $configFilePropertyName, $designTimeProviderClass, $viewTemplates = [])
    {
        if (!$designTimeProviderClass) {
            $designTimeProviderClass = self::DEFAULT_DESIGN_TIME_PROVIDER;
        }

        $this->behaviors[$class] = [
            'class' => $class,
            'name' => $name,
            'properties' => $properties,
            'designTimeProvider' => $designTimeProviderClass,
            'viewTemplates' => $viewTemplates,
            'configPropertyName' => $configFilePropertyName
        ];
    }

    protected function listBehaviors()
    {
        if ($this->behaviors !== null) {
            return $this->behaviors;
        }

        $this->behaviors = [];

        Event::fire('pages.builder.registerControllerBehaviors', [$this]);

        return $this->behaviors;
    }
}