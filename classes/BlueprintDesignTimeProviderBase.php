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
     * @param  object $blueprintObj
     * @return string
     */
    abstract public function renderBlueprintBody($class, $properties, $blueprintObj);

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
