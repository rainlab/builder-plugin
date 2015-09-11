<?php namespace RainLab\Builder\Widgets;

use RainLab\Builder\Classes\ControlDesignTimeProviderBase;
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
    /**
     * Renders conrol body.
     * @param string $type Specifies the control type to render.
     * @param array $properties Control property values.
     * @return string Returns HTML markup string.
     */
    public function renderControlBody($type, $properties)
    {
        return $type;
    }

    /**
     * Determines whether a control supports default labels and comments.
     * @param string $type Specifies the control type.
     * @return boolean
     */
    public function controlHasLabels($type)
    {
        return true;
    }
}