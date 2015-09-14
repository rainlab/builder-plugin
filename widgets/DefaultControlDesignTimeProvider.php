<?php namespace RainLab\Builder\Widgets;

use RainLab\Builder\Classes\ControlDesignTimeProviderBase;
use SystemException;
use Input;
use Response;
use Request;
use Str;
use Lang;

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
        'widget'
    ];

    /**
     * Renders conrol body.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @return string Returns HTML markup string.
     */
    public function renderControlBody($type, $properties)
    {
        if (!in_array($type, $this->defaultControlsTypes)) {
            $this->renderUnknownControl($type, $properties);
        }

        return $this->makePartial('control-'.$type, [
            'properties'=>$properties
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

    protected function renderUnknownControl($type, $properties)
    {
        throw new SystemException('To implement - rendering of unknown controls');
    }
}