<?php namespace RainLab\Builder\Widgets;

use RainLab\Builder\Classes\ControlDesignTimeProviderBase;
use SystemException;
use Input;
use Response;
use Request;
use Str;
use Lang;
use File;

/**
 * Database table list widget.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DefaultControlDesignTimeProvider extends ControlDesignTimeProviderBase
{
    protected $defaultControlsTypes = [
        'text',
        'password',
        'textarea',
        'checkbox',
        'dropdown',
        'radio',
        'checkboxlist',
        'switch',
        'section',
        'partial',
        'hint',
        'widget',
        'repeater'
    ];

    /**
     * Renders conrol body.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @param  RainLab\Builder\FormWidgets\FormBuilder $formBuilder FormBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderControlBody($type, $properties, $formBuilder)
    {
        if (!in_array($type, $this->defaultControlsTypes)) {
            return $this->renderUnknownControl($type, $properties);
        }

        return $this->makePartial('control-'.$type, [
            'properties'=>$properties,
            'formBuilder' => $formBuilder
        ]);
    }

    /**
     * Renders conrol static body.
     * The control static body is never updated with AJAX during the form editing.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @param  RainLab\Builder\FormWidgets\FormBuilder $formBuilder FormBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderControlStaticBody($type, $properties, $formBuilder)
    {
        if (!in_array($type, $this->defaultControlsTypes)) {
            return null;
        }

        $partialName = 'control-static-'.$type;
        $partialPath = $this->getViewPath('_'.$partialName.'.htm');

        if (!File::exists($partialPath)) {
            return null;
        }

        return $this->makePartial($partialName, [
            'properties'=>$properties,
            'formBuilder' => $formBuilder
        ]);
    }

    /**
     * Determines whether a control supports default labels and comments.
     * @param string $type Specifies the control type.
     * @return boolean
     */
    public function controlHasLabels($type)
    {
        if (in_array($type, ['checkbox', 'switch'])) {
            return false;
        }

        return true;
    }

    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }

    protected function renderUnknownControl($type, $properties)
    {
        return $this->makePartial('control-unknowncontrol', [
            'properties'=>$properties,
            'type'=>$type
        ]);
    }
}