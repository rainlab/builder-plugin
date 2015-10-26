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
class FormBuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'formbuilder';

    protected $designTimeProviders = [];

    protected $tabConfigurationSchema = null;

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
        $library = ControlLibrary::instance();
        $controls = $library->listControls();
        $this->vars['registeredControls'] = $controls;
        $this->vars['model'] = $this->model;
        $this->vars['controlGroups'] = array_keys($controls);
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/formbuilder.js', 'builder');
        $this->addJs('js/formbuilder.domtopropertyjson.js', 'builder');
        $this->addJs('js/formbuilder.tabs.js', 'builder');
    }

    public function renderContainer($fieldsConfiguration)
    {
        return $this->makePartial('container', ['fieldsConfiguration' => $fieldsConfiguration]);
    }

    /*
     * Event handlers
     */

    public function onModelFormRenderControlWrapper()
    {
        $type = Input::get('controlType');
        $controlId = Input::get('controlId');
        $properties = Input::get('properties');

        $controlInfo = $this->getControlInfo($type);

        return [
            'markup' => $this->renderControlWrapper($type, $properties),
            'controlId' => $controlId,
            'controlTitle' => Lang::get($controlInfo['name']),
            'description' => Lang::get($controlInfo['description']),
            'type' => $type
        ];
    }

    public function onModelFormRenderControlBody()
    {
        $type = Input::get('controlType');
        $controlId = Input::get('controlId');
        $properties = Input::get('properties');

        return [
            'markup' => $this->renderControlBody($type, $properties),
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

    protected function propertiesToInspectorSchema($propertyConfiguration)
    {
        $result = [];

        $fieldNameProperty = [
            'title' => Lang::get('rainlab.builder::lang.form.property_field_name_title'),
            'property' => 'oc.fieldName',
            'validation' => [
                'required' => [
                    'message' => Lang::get('rainlab.builder::lang.form.property_field_name_required')
                ],
                'regex' => [
                    'message' => Lang::get('rainlab.builder::lang.form.property_field_name_regex'),
                    'pattern' => '^[a-zA-Z]+[0-9a-z\_]*$'
                ]
            ]
        ];

        $result[] = $fieldNameProperty;

        foreach ($propertyConfiguration as $property=>$propertyData) {
            $propertyData['property'] = $property;

            $result[] = $propertyData;
        }

        return $result;
    }

    protected function getControlInfo($type)
    {
        $library = ControlLibrary::instance();
        $controlInfo = $library->getControlInfo($type);

        if (!$controlInfo) {
            throw new ApplicationException('The requested control type is not found.');
        }

        return $controlInfo;
    }

    protected function renderControlBody($type, $properties)
    {
        $controlInfo = $this->getControlInfo($type);
        $provider = $this->getControlDesignTimeProvider($controlInfo['designTimeProvider']);

        return $this->makePartial('controlbody', [
            'hasLabels' => $provider->controlHasLabels($type),
            'body' => $provider->renderControlBody($type, $properties),
            'properties' => $properties
        ]);
    }

    protected function renderControlWrapper($type, $properties = [])
    {
        // This method renders the control completely, including 
        // the wrapping element.

        $controlInfo = $this->getControlInfo($type);

        $provider = $this->getControlDesignTimeProvider($controlInfo['designTimeProvider']);
        return $this->makePartial('controlwrapper', [
            'fieldsConfiguration' => $this->propertiesToInspectorSchema($controlInfo['properties']),
            'type' => $type, 
            'properties' => $properties
        ]);
    }

    protected function getSpan($currentSpan, $prevSpan)
    {
        if ($currentSpan == 'auto' || !strlen($currentSpan)) {
            if ($prevSpan == 'left') {
                return 'right';
            }
            else {
                return  'left';
            }
        }

        return $currentSpan;
    }

    protected function getControlRenderingInfo($controlName, $properties, $prevProperties)
    {
        $type = isset($properties['type']) ? $properties['type'] : 'text';
        $spanFixed = isset($properties['span']) ? $properties['span'] : 'auto';
        $prevSpan = isset($prevProperties['span']) ? $prevProperties['span'] : 'auto';

        $span = $this->getSpan($spanFixed, $prevSpan);
        $spanClass = 'span-'.$span;

        $properties['oc.fieldName'] = $controlName;

        $controlInfo = $this->getControlInfo($type);

        return [
            'title' => Lang::get($controlInfo['name']),
            'description' => Lang::get($controlInfo['description']),
            'type' => $type,
            'span' => $span,
            'spanFixed' => $spanFixed,
            'spanClass' => $spanClass,
            'properties' => $properties
        ];
    }

    protected function getTabConfigurationSchema()
    {
        if ($this->tabConfigurationSchema !== null) {
            return $this->tabConfigurationSchema;
        }

        $result = [
            [
                'title' => Lang::get('rainlab.builder::lang.form.tab_title'),
                'property' => 'title',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_tab_title_required')
                    ]
                ]
            ]
        ];

        return $this->tabConfigurationSchema = json_encode($result);
    }

    protected function getTabConfigurationValues($values)
    {
        return json_encode($values);
    }
}