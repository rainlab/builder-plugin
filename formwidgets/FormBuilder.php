<?php namespace RainLab\Builder\FormWidgets;

use Request;
use Backend\Classes\FormWidgetBase;
use RainLab\Builder\Classes\ControlLibrary;
use ApplicationException;
use Input;

/**
 * Menu items widget.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class FormBuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'formbuilder';

    protected $designTimeProviders = [];

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

    public function renderControl($type, $properties = [])
    {
        $library = new ControlLibrary($type);
        $controlInfo = $library->getControlInfo($type);

        if (!$controlInfo) {
            throw new ApplicationException('The requested control type is not found.');
        }

        $provider = $this->getControlDesignTimeProvider($controlInfo['designTimeProvider']);
        return $this->makePartial('control', [
            'hasLabels' => $provider->controlHasLabels($type),
            'body' => $provider->renderControlBody($type, $properties),
            'properties' => $properties
        ]);
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $library = new ControlLibrary();
        $controls = $library->listControls();
        $this->vars['registeredControls'] = $controls;
        $this->vars['controlGroups'] = array_keys($controls);
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/formbuilder.js', 'builder');
    }

    public function renderContainer($fieldsConfiguration)
    {
        return $this->makePartial('container', ['fieldsConfiguration' => $fieldsConfiguration]);
    }

    /*
     * Event handlers
     */

    public function onModelFormRenderField()
    {
        $type = Input::get('controlType');
        $controlId = Input::get('controlId');
        $properties = Input::get('properties');

        return [
            'markup' => $this->renderControl($type, $properties),
            'controlId' => $controlId
        ];
    }

    //
    // Methods for the internal use
    //

    protected function getControlDesignTimeProvider($providerClass)
    {
        if (array_key_exists($providerClass, $this->designTimeProviders)) {
            return $this->designTimeProviders[$providerClass];
        }

        return $this->designTimeProviders[$providerClass] = new $providerClass($this->controller);
    }

    protected function getPropertyValue($properties, $property)
    {
        if (array_key_exists($property, $properties)) {
            return $properties[$property];
        }

        return null;
    }
}