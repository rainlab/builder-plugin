<?php namespace RainLab\Builder\Classes;

use Backend\Classes\WidgetBase;

abstract class BehaviorDesignTimeProviderBase extends WidgetBase 
{
    /**
     * Renders behaivor body.
     * @param string $class Specifies the behavior class to render.
     * @param array $properties Behavior property values.
     * @param  \RainLab\Builder\FormWidgets\ControllerBuilder $controllerBuilder ControllerBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    abstract public function renderBehaviorBody($class, $properties, $controllerBuilder);

    /**
     * Returns default behavior configuration as an array.
     * @param string $class Specifies the behavior class name.
     * @param string $controllerModel Controller model.
     * @param mixed $controllerGenerator Controller generator object.
     * @return array Returns the behavior configuration array.
     */
    abstract public function getDefaultConfiguration($class, $controllerModel, $controllerGenerator);

    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }

}