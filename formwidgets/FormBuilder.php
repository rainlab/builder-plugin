<?php namespace RainLab\Builder\FormWidgets;

use Backend\Classes\FormWidgetBase;
use RainLab\Builder\Classes\ControlLibrary;
use ApplicationException;
use Input;
use Lang;

/**
 * Form builder widget.
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

    protected $tabsConfigurationSchema = null;

    protected $controlInfoCache = [];

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
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addJs('js/formbuilder.js', 'builder');
        $this->addJs('js/formbuilder.domtopropertyjson.js', 'builder');
        $this->addJs('js/formbuilder.tabs.js', 'builder');
        $this->addJs('js/formbuilder.controlpalette.js', 'builder');
    }

    public function renderControlList($controls, $listName = '')
    {
        return $this->makePartial('controllist', [
            'controls' => $controls,
            'listName' => $listName
        ]);
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
            'markup' => $this->renderControlBody($type, $properties, $this),
            'controlId' => $controlId
        ];
    }

    public function onModelFormLoadControlPalette()
    {
        $controlId = Input::get('controlId');

        $library = ControlLibrary::instance();
        $controls = $library->listControls();
        $this->vars['registeredControls'] = $controls;
        $this->vars['controlGroups'] = array_keys($controls);

        return [
            'markup' => $this->makePartial('controlpalette'),
            'controlId' => $controlId
        ];
    }

    public function getPluginCode()
    {
        $pluginCode = Input::get('plugin_code');
        if (strlen($pluginCode)) {
            return $pluginCode;
        }

        return $this->model->getPluginCodeObj()->toCode();
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
            'type' => 'autocomplete',
            'fillFrom' => 'model-fields',
            'validation' => [
                'required' => [
                    'message' => Lang::get('rainlab.builder::lang.form.property_field_name_required')
                ],
                'regex' => [
                    'message' => Lang::get('rainlab.builder::lang.form.property_field_name_regex'),
                    'pattern' => '^[a-zA-Z\_]+[0-9a-z\_\[\]]*$'
                ]
            ]
        ];

        $result[] = $fieldNameProperty;

        foreach ($propertyConfiguration as $property => $propertyData) {
            $propertyData['property'] = $property;

            if ($propertyData['type'] === 'control-container') {
                // Control container type properties are handled with the form builder UI and
                // should not be available in Inspector.
                //
                continue;
            }

            $result[] = $propertyData;
        }

        return $result;
    }

    protected function getControlInfo($type)
    {
        if (array_key_exists($type, $this->controlInfoCache)) {
            return $this->controlInfoCache[$type];
        }

        $library = ControlLibrary::instance();
        $controlInfo = $library->getControlInfo($type);

        if (!$controlInfo) {
            throw new ApplicationException('The requested control type is not found.');
        }

        return $this->controlInfoCache[$type] = $controlInfo;
    }

    protected function renderControlBody($type, $properties)
    {
        $controlInfo = $this->getControlInfo($type);
        $provider = $this->getControlDesignTimeProvider($controlInfo['designTimeProvider']);

        return $this->makePartial('controlbody', [
            'hasLabels' => $provider->controlHasLabels($type),
            'body' => $provider->renderControlBody($type, $properties, $this),
            'properties' => $properties
        ]);
    }

    protected function renderControlStaticBody($type, $properties, $controlConfiguration)
    {
        // The control body footer is never updated with AJAX and currently
        // used only by the Repeater widget to display its controls.

        $controlInfo = $this->getControlInfo($type);
        $provider = $this->getControlDesignTimeProvider($controlInfo['designTimeProvider']);

        return $provider->renderControlStaticBody($type, $properties, $controlConfiguration, $this);
    }

    protected function renderControlWrapper($type, $properties = [], $controlConfiguration = [])
    {
        // This method renders the entire control, including
        // the wrapping element.

        $controlInfo = $this->getControlInfo($type);

        // Builder UI displays Comment and Comment Above properties
        // as Comment and Comment Position properties.

        if (array_key_exists('comment', $properties) && strlen($properties['comment'])) {
            $properties['oc.comment'] = $properties['comment'];
            $properties['oc.commentPosition'] = 'below';
        }

        if (array_key_exists('commentAbove', $properties) && strlen($properties['commentAbove'])) {
            $properties['oc.comment'] = $properties['commentAbove'];
            $properties['oc.commentPosition'] = 'above';
        }

        $provider = $this->getControlDesignTimeProvider($controlInfo['designTimeProvider']);
        return $this->makePartial('controlwrapper', [
            'fieldsConfiguration' => $this->propertiesToInspectorSchema($controlInfo['properties']),
            'controlConfiguration' => $controlConfiguration,
            'type' => $type,
            'properties' => $properties
        ]);
    }

    protected function getSpan($currentSpan, $prevSpan, $isPlaceholder = false)
    {
        if ($currentSpan == 'auto' || !strlen($currentSpan)) {
            if ($prevSpan == 'left') {
                return 'right';
            }
            else {
                return $isPlaceholder ? 'full' : 'left';
            }
        }

        return $currentSpan;
    }

    protected function preprocessPropertyValues($controlName, $properties, $controlInfo)
    {
        $properties['oc.fieldName'] = $controlName;

        // Remove the control container type property values.
        //
        if (isset($controlInfo['properties'])) {
            foreach ($controlInfo['properties'] as $property => $propertyConfig) {
                if (isset($propertyConfig['type']) && $propertyConfig['type'] === 'control-container' && isset($properties[$property])) {
                    unset($properties[$property]);
                }
            }
        }

        return $properties;
    }

    protected function getControlRenderingInfo($controlName, $properties, $prevProperties)
    {
        $type = isset($properties['type']) ? $properties['type'] : 'text';
        $spanFixed = isset($properties['span']) ? $properties['span'] : 'auto';
        $prevSpan = isset($prevProperties['span']) ? $prevProperties['span'] : 'auto';

        $span = $this->getSpan($spanFixed, $prevSpan);
        $spanClass = 'span-'.$span;

        $controlInfo = $this->getControlInfo($type);

        $properties = $this->preprocessPropertyValues($controlName, $properties, $controlInfo);

        return [
            'title' => Lang::get($controlInfo['name']),
            'description' => Lang::get($controlInfo['description']),
            'type' => $type,
            'span' => $span,
            'spanFixed' => $spanFixed,
            'spanClass' => $spanClass,
            'properties' => $properties,
            'unknownControl' => isset($controlInfo['unknownControl']) && $controlInfo['unknownControl']
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
                'type' => 'builderLocalization',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_tab_title_required')
                    ]
                ]
            ]
        ];

        return $this->tabConfigurationSchema = json_encode($result);
    }

    protected function getTabsConfigurationSchema()
    {
        if ($this->tabsConfigurationSchema !== null) {
            return $this->tabsConfigurationSchema;
        }

        $result = [
            [
                'title' => Lang::get('rainlab.builder::lang.form.tab_stretch'),
                'description' => Lang::get('rainlab.builder::lang.form.tab_stretch_description'),
                'property' => 'stretch',
                'type' => 'checkbox'
            ],
            [
                'title' => Lang::get('rainlab.builder::lang.form.tab_css_class'),
                'description' => Lang::get('rainlab.builder::lang.form.tab_css_class_description'),
                'property' => 'cssClass',
                'type' => 'string'
            ]
        ];

        return $this->tabsConfigurationSchema = json_encode($result);
    }

    protected function getTabConfigurationValues($values)
    {
        if (!count($values)) {
            return '{}';
        }

        return json_encode($values);
    }

    protected function getTabsConfigurationValues($values)
    {
        if (!count($values)) {
            return '{}';
        }

        return json_encode($values);
    }

    protected function getTabsFields($tabsName, $fields)
    {
        $result = [];

        if (!is_array($fields)) {
            return $result;
        }

        if (!array_key_exists($tabsName, $fields) || !array_key_exists('fields', $fields[$tabsName])) {
            return $result;
        }

        $defaultTab = Lang::get('backend::lang.form.undefined_tab');
        if (array_key_exists('defaultTab', $fields[$tabsName])) {
            $defaultTab = Lang::get($fields[$tabsName]['defaultTab']);
        }

        foreach ($fields[$tabsName]['fields'] as $fieldName => $fieldConfiguration) {
            if (!isset($fieldConfiguration['tab'])) {
                $fieldConfiguration['tab'] = $defaultTab;
            }

            $tab = $fieldConfiguration['tab'];
            if (!array_key_exists($tab, $result)) {
                $result[$tab] = [];
            }

            $result[$tab][$fieldName] = $fieldConfiguration;
        }

        return $result;
    }
}
