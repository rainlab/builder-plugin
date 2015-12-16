<?php namespace RainLab\Builder\Classes;

use Lang;

/**
 * Utility class for registering standard controller behaviors.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class StandardBehaviorsRegistry
{
    protected $behaviorLibrary;

    public function __construct($behaviorLibrary)
    {
        $this->behaviorLibrary = $behaviorLibrary;

        $this->registerBehaviors();
    }

    protected function registerBehaviors()
    {
        $this->registerListBehavior();
        $this->registerFormBehavior();
    }

    protected function registerListBehavior()
    {
        $this->behaviorLibrary->registerBehavior(
            $class,
            $name,
            $properties,
            $configFilePropertyName,
            $designTimeProviderClass,
            $viewTemplates = []);
    }
}