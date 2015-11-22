<?php namespace RainLab\Builder\FormWidgets;

use Request;
use Backend\Classes\FormWidgetBase;
use RainLab\Builder\Classes\ControlLibrary;
use ApplicationException;
use Input;
use Lang;

/**
 * Menu items widget.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class MenuEditor extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'menueditor';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('body');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['model'] = $this->model;
        $this->vars['items'] = $this->model->menus;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
    }

    //
    // Event handlers
    //

    //
    // Methods for the internal use
    //

    protected function getItemArrayProperty($item, $property)
    {
        if (array_key_exists($property, $item)) {
            return $item[$property];
        }

        return null;
    }
}