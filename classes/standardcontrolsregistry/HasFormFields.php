<?php namespace RainLab\Builder\Classes\StandardControlsRegistry;

use Lang;
use RainLab\Builder\Classes\ControlLibrary;

/**
 * HasFormFields
 */
trait HasFormFields
{
    /**
     * registerTextControl
     */
    protected function registerTextControl()
    {
        $this->controlLibrary->registerControl(
            'text',
            'rainlab.builder::lang.form.control_text',
            'rainlab.builder::lang.form.control_text_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-terminal',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

    /**
     * registerNumberControl
     */
    protected function registerNumberControl()
    {
        // Extra properties
        $extraProps = [
            'min' => [
                'title' => "Min",
                'description' => "The client-side minimum value.",
                'type' => 'string',
                'ignoreIfEmpty' => true,
            ],
            'max' => [
                'title' => "Max",
                'description' => "The client-side maximum value.",
                'type' => 'string',
                'ignoreIfEmpty' => true,
            ],
            'step' => [
                'title' => "Step",
                'description' => "The client-side step increment.",
                'type' => 'string',
                'ignoreIfEmpty' => true,
            ],
        ];

        $this->controlLibrary->registerControl(
            'number',
            'rainlab.builder::lang.form.control_number',
            'rainlab.builder::lang.form.control_number_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-superscript',
            $this->controlLibrary->getStandardProperties(['stretch'], $extraProps),
            null
        );
    }

    /**
     * registerPasswordControl
     */
    protected function registerPasswordControl()
    {
        $this->controlLibrary->registerControl(
            'password',
            'rainlab.builder::lang.form.control_password',
            'rainlab.builder::lang.form.control_password_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-lock',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

    /**
     * registerEmailControl
     */
    protected function registerEmailControl()
    {
        $this->controlLibrary->registerControl(
            'email',
            'rainlab.builder::lang.form.control_email',
            'rainlab.builder::lang.form.control_email_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-envelope',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

    /**
     * registerTextareaControl
     */
    protected function registerTextareaControl()
    {
        $properties = $this->getFieldSizeProperties();

        $this->controlLibrary->registerControl(
            'textarea',
            'rainlab.builder::lang.form.control_textarea',
            'rainlab.builder::lang.form.control_textarea_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-pencil-square-o',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    /**
     * registerDropdownControl
     */
    protected function registerDropdownControl()
    {
        $properties = [
            'options' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
            'optionsMethod' => [
                'title' => "Options Method",
                'description' => "Request options from this method name defined on the model or as a static method (Class::method).",
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'emptyOption' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_empty_option'),
                'description' => Lang::get('rainlab.builder::lang.form.property_empty_option_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 83
            ],
            'showSearch' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_show_search'),
                'description' => Lang::get('rainlab.builder::lang.form.property_show_search_description'),
                'type' => 'checkbox',
                'sortOrder' => 84,
                'default' => true
            ]
        ];

        $this->controlLibrary->registerControl(
            'dropdown',
            'rainlab.builder::lang.form.control_dropdown',
            'rainlab.builder::lang.form.control_dropdown_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-angle-double-down',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    /**
     * registerRadioListControl
     */
    protected function registerRadioListControl()
    {
        $properties = [
            'options' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
            'optionsMethod' => [
                'title' => "Options Method",
                'description' => "Request options from this method name defined on the model or as a static method (Class::method).",
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
        ];

        $excludeProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'preset'
        ];

        $this->controlLibrary->registerControl(
            'radio',
            'rainlab.builder::lang.form.control_radio',
            'rainlab.builder::lang.form.control_radio_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-dot-circle-o',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerBalloonSelectorControl
     */
    protected function registerBalloonSelectorControl()
    {
        $properties = [
            'options' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
            'optionsMethod' => [
                'title' => "Options Method",
                'description' => "Request options from this method name defined on the model or as a static method (Class::method).",
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
        ];

        $this->controlLibrary->registerControl(
            'balloon-selector',
            'rainlab.builder::lang.form.control_balloon-selector',
            'rainlab.builder::lang.form.control_balloon-selector_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-ellipsis-h',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    /**
     * registerCheckboxControl
     */
    protected function registerCheckboxControl()
    {
        $this->controlLibrary->registerControl(
            'checkbox',
            'rainlab.builder::lang.form.control_checkbox',
            'rainlab.builder::lang.form.control_checkbox_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-check-square-o',
            $this->controlLibrary->getStandardProperties(['oc.commentPosition', 'stretch'], $this->getCheckboxTypeProperties()),
            null
        );
    }

    /**
     * registerCheckboxListControl
     */
    protected function registerCheckboxListControl()
    {
        $properties = [
            'options' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
            'optionsMethod' => [
                'title' => "Options Method",
                'description' => "Request options from this method name defined on the model or as a static method (Class::method).",
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'quickselect' =>  [
                'title' => "Quick Select",
                'description' => "Show the quick selection buttons.",
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'inlineOptions' =>  [
                'title' => "Inline Options",
                'description' => "Display the options side-by-side instead of stacked, when less than 10 options.",
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ]
        ];

        $excludeProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'preset'
        ];

        $this->controlLibrary->registerControl(
            'checkboxlist',
            'rainlab.builder::lang.form.control_checkboxlist',
            'rainlab.builder::lang.form.control_checkboxlist_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-list',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerSwitchControl
     */
    protected function registerSwitchControl()
    {
        $this->controlLibrary->registerControl(
            'switch',
            'rainlab.builder::lang.form.control_switch',
            'rainlab.builder::lang.form.control_switch_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-toggle-on',
            $this->controlLibrary->getStandardProperties(['oc.commentPosition', 'stretch'], $this->getCheckboxTypeProperties()),
            null
        );
    }

    /**
     * getCheckboxTypeProperties
     */
    protected function getCheckboxTypeProperties()
    {
        return [
            'default' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_checked_default_title'),
                'type' => 'checkbox'
            ]
        ];
    }
}
