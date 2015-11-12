<?php namespace RainLab\Builder\Classes;

use Lang;

/**
 * Utility class for registering standard back-end controls.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class StandardControlsRegistry
{
    protected $controlLibrary;

    public function __construct($controlLibrary)
    {
        $this->controlLibrary = $controlLibrary;

        $this->registerControls();
    }

    protected function registerControls()
    {
        // Controls
        //
        $this->registerTextControl();
        $this->registerPasswordControl();
        $this->registerNumberControl();
        $this->registerCheckboxControl();
        $this->registerSwitchControl();
        $this->registerTextareaControl();
        $this->registerDropdownControl();
        $this->registerHintControl();
        $this->registerPartialControl();
        $this->registerSectionControl();
        $this->registerRadioListControl();
        $this->registerCheckboxListControl();

        // Widgets
        //
        $this->registerRepeaterWidget();
    }

    protected function registerTextControl()
    {
        $this->controlLibrary->registerControl('text', 
            'rainlab.builder::lang.form.control_text',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-terminal',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

    protected function registerPasswordControl()
    {
        $this->controlLibrary->registerControl('password', 
            'rainlab.builder::lang.form.control_password',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-lock',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

    protected function registerNumberControl()
    {
        $this->controlLibrary->registerControl('number', 
            'rainlab.builder::lang.form.control_number',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-superscript',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

    protected function registerCheckboxControl()
    {
        $this->controlLibrary->registerControl('checkbox', 
            'rainlab.builder::lang.form.control_checkbox',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-check-square-o',
            $this->controlLibrary->getStandardProperties(['oc.commentPosition', 'stretch'], $this->getCheckboxTypeProperties()),
            null
        );
    }

    protected function registerSwitchControl()
    {
        $this->controlLibrary->registerControl('switch', 
            'rainlab.builder::lang.form.control_switch',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-toggle-on',
            $this->controlLibrary->getStandardProperties(['oc.commentPosition', 'stretch'], $this->getCheckboxTypeProperties()),
            null
        );
    }

    protected function registerTextareaControl()
    {
        $properties = [
            'size' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_attributes_size'),
                'type' => 'dropdown',
                'options' => [
                    'tiny' => Lang::get('rainlab.builder::lang.form.property_attributes_size_tiny'),
                    'small' => Lang::get('rainlab.builder::lang.form.property_attributes_size_small'),
                    'large' => Lang::get('rainlab.builder::lang.form.property_attributes_size_large'),
                    'huge' => Lang::get('rainlab.builder::lang.form.property_attributes_size_huge'),
                    'giant' => Lang::get('rainlab.builder::lang.form.property_attributes_size_giant')
                ]
            ]
        ];

        $this->controlLibrary->registerControl('textarea', 
            'rainlab.builder::lang.form.control_textarea',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-pencil-square-o',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    protected function registerDropdownControl()
    {
        $properties = [
            'options' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true
            ]
        ];

        $this->controlLibrary->registerControl('dropdown', 
            'rainlab.builder::lang.form.control_dropdown',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-angle-double-down',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    protected function registerHintControl()
    {
        $properties = [
            'path' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_hint_path'),
                'description' => Lang::get('rainlab.builder::lang.form.property_hint_path_description'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_hint_path_required')
                    ]
                ]
            ]
        ];

        $this->controlLibrary->registerControl('hint', 
            'rainlab.builder::lang.form.control_hint',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-question-circle',
            $this->controlLibrary->getStandardProperties($this->getPartialIgnoreProperties(), $properties),
            null
        );
    }

    protected function registerPartialControl()
    {
        $properties = [
            'path' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_partial_path'),
                'description' => Lang::get('rainlab.builder::lang.form.property_partial_path_description'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_partial_path_required')
                    ]
                ]
            ]
        ];

        $this->controlLibrary->registerControl('partial', 
            'rainlab.builder::lang.form.control_partial',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-file-text-o',
            $this->controlLibrary->getStandardProperties($this->getPartialIgnoreProperties(), $properties),
            null
        );
    }

    protected function registerSectionControl()
    {
        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'required',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'oc.commentPosition',
            'disabled'
        ];

        $this->controlLibrary->registerControl('section', 
            'rainlab.builder::lang.form.control_section',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-minus',
            $this->controlLibrary->getStandardProperties($ignoreProperties),
            null
        );
    }

    protected function registerRadioListControl()
    {
        $properties = [
            'options' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true
            ]
        ];

        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'preset'
        ];

        $this->controlLibrary->registerControl('radio', 
            'rainlab.builder::lang.form.control_radio',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-dot-circle-o',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerCheckboxListControl()
    {
        $properties = [
            'options' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true
            ]
        ];

        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'preset'
        ];

        $this->controlLibrary->registerControl('checkboxlist', 
            'rainlab.builder::lang.form.control_checkboxlist',
            null,
            ControlLibrary::GROUP_STANDARD,
            'icon-list',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function getCheckboxTypeProperties()
    {
        return [
            'default' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_checked_default_title'),
                'type' => 'checkbox'
            ]
        ];
    }

    protected function getPartialIgnoreProperties()
    {
        return [
            'stretch',
            'default',
            'placeholder',
            'required',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'label',
            'oc.commentPosition',
            'oc.comment',
            'disabled'
        ];
    }

    protected function registerRepeaterWidget()
    {
        $properties = [
            'prompt' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_prompt'),
                'description' => Lang::get('rainlab.builder::lang.form.property_prompt_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'default' => Lang::get('rainlab.builder::lang.form.property_prompt_default'),
            ],
            'form' => [
                'type' => 'control-container'
            ]
        ];

        $ignoreProperties = [
            'stretch',
            'placeholder',
            'default',
            'required',
            'defaultFrom',
            'dependsOn',
            'trigger', 
            'preset',
            'attributes'
        ];

        $this->controlLibrary->registerControl('repeater', 
            'rainlab.builder::lang.form.control_repeater',
            null,
            ControlLibrary::GROUP_WIDGETS,
            'icon-server',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }
}