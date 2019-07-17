<?php namespace RainLab\Builder\Classes;

use Backend\Classes\WidgetBase;

abstract class ControlDesignTimeProviderBase extends WidgetBase
{
    /**
     * Renders conrol body.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @param  \RainLab\Builder\FormWidgets\FormBuilder $formBuilder FormBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    abstract public function renderControlBody($type, $properties, $formBuilder);

    /**
     * Renders conrol static body.
     * The control static body is never updated with AJAX during the form editing.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @param array $controlConfiguration Raw control property values.
     * @param  \RainLab\Builder\FormWidgets\FormBuilder $formBuilder FormBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    abstract public function renderControlStaticBody($type, $properties, $controlConfiguration, $formBuilder);

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
