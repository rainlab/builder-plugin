<?php namespace RainLab\Builder\Classes;

use Backend\Classes\WidgetBase;

abstract class BehaviorDesignTimeProviderBase extends WidgetBase 
{
    /**
     * Renders behaivor body.
     * @param string $class Specifies the behavior class to render.
     * @param array $properties Behavior property values.
     * @param  RainLab\Builder\FormWidgets\ControllerBuilder $controllerBuilder ControllerBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    abstract public function renderBehaviorBody($class, $properties, $controllerBuilder);

    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }
}