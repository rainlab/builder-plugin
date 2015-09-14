<?php namespace RainLab\Builder\Classes;

use Backend\Classes\WidgetBase;

abstract class ControlDesignTimeProviderBase extends WidgetBase 
{
    /**
     * Renders conrol body.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @return string Returns HTML markup string.
     */
    abstract public function renderControlBody($type, $properties);

    /**
     * Determines whether a control supports default labels and comments.
     * @param string $type Specifies the control type.
     * @return boolean
     */
    abstract public function controlHasLabels($type);

    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }
}