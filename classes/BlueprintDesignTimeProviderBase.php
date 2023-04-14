<?php namespace RainLab\Builder\Classes;

use Backend\Classes\WidgetBase;

/**
 * BlueprintDesignTimeProviderBase
 */
abstract class BlueprintDesignTimeProviderBase extends WidgetBase
{
    /**
     * renderBlueprintBody
     * @param string $class Specifies the behavior class to render.
     * @param array $properties Blueprint property values.
     * @param  \RainLab\Builder\FormWidgets\ControllerBuilder $controllerBuilder ControllerBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    abstract public function renderBlueprintBody($class, $properties, $controllerBuilder);

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
