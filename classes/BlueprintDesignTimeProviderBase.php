<?php namespace RainLab\Builder\Classes;

use Backend\Classes\WidgetBase;

/**
 * BlueprintDesignTimeProviderBase
 */
abstract class BlueprintDesignTimeProviderBase extends WidgetBase
{
    /**
     * Renders behaivor body.
     * @param string $class Specifies the behavior class to render.
     * @param array $properties Blueprint property values.
     * @param  \RainLab\Builder\FormWidgets\ControllerBuilder $controllerBuilder ControllerBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    abstract public function renderBlueprintBody($class, $properties, $controllerBuilder);

    /**
     * Returns default behavior configuration as an array.
     * @param string $class Specifies the behavior class name.
     * @param string $controllerModel Controller model.
     * @param mixed $controllerGenerator Controller generator object.
     * @return array Returns the behavior configuration array.
     */
    abstract public function getDefaultConfiguration($class, $controllerModel, $controllerGenerator);

    /**
     * getPropertyValue
     */
    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }
}
