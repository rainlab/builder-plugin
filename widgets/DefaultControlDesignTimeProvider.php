<?php namespace RainLab\Builder\Widgets;

use File;
use RainLab\Builder\Classes\ControlDesignTimeProviderBase;

/**
 * DefaultControlDesignTimeProvider is a default control design-time provider.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DefaultControlDesignTimeProvider extends ControlDesignTimeProviderBase
{
    /**
     * @var array defaultControlsTypes
     */
    protected $defaultControlsTypes = [
        'text',
        'number',
        'password',
        'email',
        'textarea',
        'checkbox',
        'dropdown',
        'balloon-selector',
        'radio',
        'checkboxlist',
        'switch',
        'section',
        'ruler',
        'partial',
        'hint',
        'widget',
        'codeeditor',
        'colorpicker',
        'datatable',
        'datepicker',
        'fileupload',
        'markdown',
        'mediafinder',
        'nestedform',
        'recordfinder',
        'relation',
        'repeater',
        'richeditor',
        'pagefinder',
        'sensitive',
        'taglist',
    ];

    /**
     * renderControlBody
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @param  \RainLab\Builder\FormWidgets\FormBuilder $formBuilder FormBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderControlBody($type, $properties, $formBuilder)
    {
        if (!in_array($type, $this->defaultControlsTypes)) {
            return $this->renderUnknownControl($type, $properties);
        }

        return $this->makePartial('control-'.$type, [
            'properties' => $properties,
            'formBuilder' => $formBuilder
        ]);
    }

    /**
     * renderControlStaticBody renders control static body.
     * The control static body is never updated with AJAX during the form editing.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values preprocessed for the Inspector.
     * @param array $controlConfiguration Raw control property values.
     * @param  \RainLab\Builder\FormWidgets\FormBuilder $formBuilder FormBuilder widget instance.
     * @return string Returns HTML markup string.
     */
    public function renderControlStaticBody($type, $properties, $controlConfiguration, $formBuilder)
    {
        if (!in_array($type, $this->defaultControlsTypes)) {
            return null;
        }

        $partialName = 'control-'.$type.'-static';
        $partialPath = $this->getViewPath('_'.$partialName.'.php');

        if (!File::exists($partialPath)) {
            return null;
        }

        return $this->makePartial($partialName, [
            'properties' => $properties,
            'controlConfiguration' => $controlConfiguration,
            'formBuilder' => $formBuilder
        ]);
    }

    /**
     * controlHasLabels determines whether a control supports default labels and comments.
     * @param string $type Specifies the control type.
     * @return boolean
     */
    public function controlHasLabels($type)
    {
        if (in_array($type, ['checkbox', 'switch', 'hint', 'partial', 'section', 'ruler'])) {
            return false;
        }

        return true;
    }

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

    /**
     * renderUnknownControl
     */
    protected function renderUnknownControl($type, $properties)
    {
        return $this->makePartial('control-unknowncontrol', [
            'properties'=>$properties,
            'type'=>$type
        ]);
    }
}
