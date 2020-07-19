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
        $this->registerBalloonSelectorControl();
        $this->registerHintControl();
        $this->registerPartialControl();
        $this->registerSectionControl();
        $this->registerRadioListControl();
        $this->registerCheckboxListControl();

        // Widgets
        //
        $this->registerCodeEditorWidget();
        $this->registerColorPickerWidget();
        $this->registerDatepickerWidget();
        $this->registerRichEditorWidget();
        $this->registerMarkdownWidget();
        $this->registerTagListWidget();
        $this->registerFileUploadWidget();
        $this->registerRecordFinderWidget();
        $this->registerMediaFinderWidget();
        $this->registerRelationWidget();
        $this->registerRepeaterWidget();
    }

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

    protected function registerNumberControl()
    {
        $this->controlLibrary->registerControl(
            'number',
            'rainlab.builder::lang.form.control_number',
            'rainlab.builder::lang.form.control_number_description',
            ControlLibrary::GROUP_STANDARD,
            'icon-superscript',
            $this->controlLibrary->getStandardProperties(['stretch']),
            null
        );
    }

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

    protected function registerDropdownControl()
    {
        $properties = [
            'emptyOption' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_empty_option'),
                'description' => Lang::get('rainlab.builder::lang.form.property_empty_option_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'options' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
            'showSearch' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_show_search'),
                'description' => Lang::get('rainlab.builder::lang.form.property_show_search_description'),
                'type' => 'checkbox',
                'sortOrder' => 83,
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

    protected function registerBalloonSelectorControl()
    {
        $properties = [
            'options' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                'type' => 'dictionary',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ]
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
                ],
                'sortOrder' => 81
            ]
        ];

        $this->controlLibrary->registerControl(
            'hint',
            'rainlab.builder::lang.form.control_hint',
            'rainlab.builder::lang.form.control_hint_description',
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
                ],
                'sortOrder' => 81
            ]
        ];

        $this->controlLibrary->registerControl(
            'partial',
            'rainlab.builder::lang.form.control_partial',
            'rainlab.builder::lang.form.control_partial_description',
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

        $this->controlLibrary->registerControl(
            'section',
            'rainlab.builder::lang.form.control_section',
            'rainlab.builder::lang.form.control_section_description',
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
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ]
        ];

        $ignoreProperties = [
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
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ]
        ];

        $ignoreProperties = [
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
                'sortOrder' => 81
            ],
            'titleFrom' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_title_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_title_from_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'form' => [
                'type' => 'control-container'
            ],
            'minItems' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_min_items'),
                'description' => Lang::get('rainlab.builder::lang.form.property_min_items_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 83,
                'validation' => [
                    'integer' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_min_items_integer'),
                        'allowNegative' => false,
                    ]
                ],
            ],
            'maxItems' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_max_items'),
                'description' => Lang::get('rainlab.builder::lang.form.property_max_items_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 84,
                'validation' => [
                    'integer' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_max_items_integer'),
                        'allowNegative' => false,
                    ]
                ],
            ],
            'style' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_style'),
                'description' => Lang::get('rainlab.builder::lang.form.property_style_description'),
                'type' => 'dropdown',
                'default' => 'default',
                'options' => [
                    'default' => Lang::get('rainlab.builder::lang.form.style_default'),
                    'collapsed' => Lang::get('rainlab.builder::lang.form.style_collapsed'),
                    'accordion' => Lang::get('rainlab.builder::lang.form.style_accordion'),
                ],
                'sortOrder' => 85,
            ]
        ];

        $ignoreProperties = [
            'stretch',
            'placeholder',
            'default',
            'required',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes'
        ];

        $this->controlLibrary->registerControl(
            'repeater',
            'rainlab.builder::lang.form.control_repeater',
            'rainlab.builder::lang.form.control_repeater_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-server',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerCodeEditorWidget()
    {
        $ignoreProperties = [
            'placeholder',
            'default',
            'defaultFrom',
            'dependsOn',
            'trigger',
            'preset',
            'attributes'
        ];

        $properties = $this->getFieldSizeProperties();

        $properties = array_merge($properties, [
            'size' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_attributes_size'),
                'type' => 'dropdown',
                'options' => [
                    'tiny' => Lang::get('rainlab.builder::lang.form.property_attributes_size_tiny'),
                    'small' => Lang::get('rainlab.builder::lang.form.property_attributes_size_small'),
                    'large' => Lang::get('rainlab.builder::lang.form.property_attributes_size_large'),
                    'huge' => Lang::get('rainlab.builder::lang.form.property_attributes_size_huge'),
                    'giant' => Lang::get('rainlab.builder::lang.form.property_attributes_size_giant')
                ],
                'sortOrder' => 81
            ],
            'language' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_code_language'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => 'php',
                'options' => [
                    'css' => 'CSS',
                    'html' => 'HTML',
                    'javascript' => 'JavaScript',
                    'less' => 'LESS',
                    'markdown' => 'Markdown',
                    'php' => 'PHP',
                    'plain_text' => 'Plain text',
                    'sass' => 'SASS',
                    'scss' => 'SCSS',
                    'twig' => 'Twig'
                ],
                'sortOrder' => 82
            ],
            'theme' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_code_theme'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_theme_use_default'),
                    'ambiance' => 'Ambiance',
                    'chaos' => 'Chaos',
                    'chrome' => 'Chrome',
                    'clouds' => 'Clouds',
                    'clouds_midnight' => 'Clouds midnight',
                    'cobalt' => 'Cobalt',
                    'crimson_editor' => 'Crimson editor',
                    'dawn' => 'Dawn',
                    'dreamweaver' => 'Dreamweaver',
                    'eclipse' => 'Eclipse',
                    'github' => 'Github',
                    'idle_fingers' => 'Idle fingers',
                    'iplastic' => 'IPlastic',
                    'katzenmilch' => 'Katzenmilch',
                    'kr_theme' => 'krTheme',
                    'kuroir' => 'Kuroir',
                    'merbivore' => 'Merbivore',
                    'merbivore_soft' => 'Merbivore soft',
                    'mono_industrial' => 'Mono industrial',
                    'monokai' => 'Monokai',
                    'pastel_on_dark' => 'Pastel on dark',
                    'solarized_dark' => 'Solarized dark',
                    'solarized_light' => 'Solarized light',
                    'sqlserver' => 'SQL server',
                    'terminal' => 'Terminal',
                    'textmate' => 'Textmate',
                    'tomorrow' => 'Tomorrow',
                    'tomorrow_night' => 'Tomorrow night',
                    'tomorrow_night_blue' => 'Tomorrow night blue',
                    'tomorrow_night_bright' => 'Tomorrow night bright',
                    'tomorrow_night_eighties' => 'Tomorrow night eighties',
                    'twilight' => 'Twilight',
                    'vibrant_ink' => 'Vibrant ink',
                    'xcode' => 'XCode'
                ],
                'sortOrder' => 83
            ],
            'showGutter' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_gutter'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'booleanValues' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    'true' => Lang::get('rainlab.builder::lang.form.property_gutter_show'),
                    'false' => Lang::get('rainlab.builder::lang.form.property_gutter_hide'),
                ],
                'sortOrder' => 84
            ],
            'wordWrap' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_wordwrap'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'booleanValues' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    'true' => Lang::get('rainlab.builder::lang.form.property_wordwrap_wrap'),
                    'false' => Lang::get('rainlab.builder::lang.form.property_wordwrap_nowrap'),
                ],
                'sortOrder' => 85
            ],
            'fontSize' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fontsize'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    '10' => '10px',
                    '11' => '11px',
                    '12' => '11px',
                    '13' => '13px',
                    '14' => '14px',
                    '16' => '16px',
                    '18' => '18px',
                    '20' => '20px',
                    '24' => '24px'
                ],
                'sortOrder' => 86
            ],
            'codeFolding' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_codefolding'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    'manual' => Lang::get('rainlab.builder::lang.form.property_codefolding_manual'),
                    'markbegin' => Lang::get('rainlab.builder::lang.form.property_codefolding_markbegin'),
                    'markbeginend' => Lang::get('rainlab.builder::lang.form.property_codefolding_markbeginend'),
                ],
                'sortOrder' => 87
            ],
            'autoClosing' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_autoclosing'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'booleanValues' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    'true' => Lang::get('rainlab.builder::lang.form.property_enabled'),
                    'false' => Lang::get('rainlab.builder::lang.form.property_disabled')
                ],
                'sortOrder' => 88
            ],
            'useSoftTabs' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_soft_tabs'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'booleanValues' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    'true' => Lang::get('rainlab.builder::lang.form.property_enabled'),
                    'false' => Lang::get('rainlab.builder::lang.form.property_disabled')
                ],
                'sortOrder' => 89
            ],
            'tabSize' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_tab_size'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'type' => 'dropdown',
                'default' => '',
                'ignoreIfEmpty' => true,
                'options' => [
                    '' => Lang::get('rainlab.builder::lang.form.property_use_default'),
                    2 => 2,
                    4 => 4,
                    8 => 8
                ],
                'sortOrder' => 90
            ],
            'readOnly' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_readonly'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'default' => 0,
                'ignoreIfEmpty' => true,
                'type' => 'checkbox'
            ]
        ]);

        $this->controlLibrary->registerControl(
            'codeeditor',
            'rainlab.builder::lang.form.control_codeeditor',
            'rainlab.builder::lang.form.control_codeeditor_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-code',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerColorPickerWidget()
    {
        $ignoreProperties = [
            'stretch'
        ];

        $properties = [
            'availableColors' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_available_colors'),
                'description' => Lang::get('rainlab.builder::lang.form.property_available_colors_description'),
                'type' => 'stringList',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ]
        ];

        $this->controlLibrary->registerControl(
            'colorpicker',
            'rainlab.builder::lang.form.control_colorpicker',
            'rainlab.builder::lang.form.control_colorpicker_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-eyedropper',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerDatepickerWidget()
    {
        $ignoreProperties = [
            'stretch'
        ];

        $properties = [
            'mode' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode'),
                'type' => 'dropdown',
                'default' => 'datetime',
                'options' => [
                    'date' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode_date'),
                    'datetime' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode_datetime'),
                    'time' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode_time')
                ],
                'sortOrder' => 81
            ],
            'minDate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_min_date'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_min_date_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'maxDate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_max_date'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_max_date_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 83
            ],
            'yearRange' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_range'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_range_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^([0-9]+|\[[0-9]{4},[0-9]{4}\])$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_range_invalid_format')
                    ]
                ],
                'sortOrder' => 84
            ],
            'format' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_format'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_format_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 85
            ]
        ];

        $this->controlLibrary->registerControl(
            'datepicker',
            'rainlab.builder::lang.form.control_datepicker',
            'rainlab.builder::lang.form.control_datepicker_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-calendar',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerRichEditorWidget()
    {
        $properties = $this->getFieldSizeProperties();

        $this->controlLibrary->registerControl(
            'richeditor',
            'rainlab.builder::lang.form.control_richeditor',
            'rainlab.builder::lang.form.control_richeditor_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-indent',
            $this->controlLibrary->getStandardProperties([], $properties),
            null
        );
    }

    protected function getFieldSizeProperties()
    {
        return [
            'size' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_attributes_size'),
                'type' => 'dropdown',
                'options' => [
                    'tiny' => Lang::get('rainlab.builder::lang.form.property_attributes_size_tiny'),
                    'small' => Lang::get('rainlab.builder::lang.form.property_attributes_size_small'),
                    'large' => Lang::get('rainlab.builder::lang.form.property_attributes_size_large'),
                    'huge' => Lang::get('rainlab.builder::lang.form.property_attributes_size_huge'),
                    'giant' => Lang::get('rainlab.builder::lang.form.property_attributes_size_giant')
                ],
                'sortOrder' => 51
            ]
        ];
    }

    protected function registerMarkdownWidget()
    {
        $properties = $this->getFieldSizeProperties();

        $properties = array_merge($properties, [
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_markdown_mode'),
                'type' => 'dropdown',
                'default' => 'tab',
                'options' => [
                    'split' => Lang::get('rainlab.builder::lang.form.property_markdown_mode_split'),
                    'tab' => Lang::get('rainlab.builder::lang.form.property_markdown_mode_tab')
                ],
                'sortOrder' => 81
            ]
        ]);

        $this->controlLibrary->registerControl(
            'markdown',
            'rainlab.builder::lang.form.control_markdown',
            'rainlab.builder::lang.form.control_markdown_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-columns',
            $this->controlLibrary->getStandardProperties([], $properties),
            null
        );
    }

    protected function registerTagListWidget()
    {
        $ignoreProperties = [
            'stretch',
            'readOnly'
        ];

        $properties = [
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_taglist_mode'),
                'description' => Lang::get('rainlab.builder::lang.form.property_taglist_mode_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'dropdown',
                'options' => [
                    'string' => Lang::get('rainlab.builder::lang.form.property_taglist_mode_string'),
                    'array' => Lang::get('rainlab.builder::lang.form.property_taglist_mode_array'),
                    'relation' => Lang::get('rainlab.builder::lang.form.property_taglist_mode_relation')
                ],
                'default' => 'string',
                'sortOrder' => 83
            ],
            'separator' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_taglist_separator'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'dropdown',
                'options' => [
                    'comma' => Lang::get('rainlab.builder::lang.form.property_taglist_separator_comma'),
                    'space' => Lang::get('rainlab.builder::lang.form.property_taglist_separator_space')
                ],
                'default' => 'comma',
                'sortOrder' => 84
            ],
            'customTags' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_taglist_custom_tags'),
                'description' => Lang::get('rainlab.builder::lang.form.property_taglist_custom_tags_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'checkbox',
                'default' => true,
                'sortOrder' => 86
            ],
            'options' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_taglist_options'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'stringList',
                'ignoreIfEmpty' => true,
                'sortOrder' => 85
            ],
            'nameFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_taglist_name_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_taglist_name_from_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 87
            ],
            'useKey' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_taglist_use_key'),
                'description' => Lang::get('rainlab.builder::lang.form.property_taglist_use_key_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'checkbox',
                'default' => false,
                'ignoreIfEmpty' => true,
                'sortOrder' => 88
            ]
        ];

        $this->controlLibrary->registerControl(
            'taglist',
            'rainlab.builder::lang.form.control_taglist',
            'rainlab.builder::lang.form.control_taglist_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-tags',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerFileUploadWidget()
    {
        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes'
        ];

        $properties = [
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_mode'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'dropdown',
                'default' => 'file',
                'options' => [
                    'file' => Lang::get('rainlab.builder::lang.form.property_fileupload_mode_file'),
                    'image' => Lang::get('rainlab.builder::lang.form.property_fileupload_mode_image')
                ],
                'sortOrder' => 81
            ],
            'prompt' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_prompt'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_prompt_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'imageWidth' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_image_width'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_image_width_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_fileupload_invalid_dimension')
                    ]
                ],
                'sortOrder' => 83
            ],
            'imageHeight' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_image_height'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_image_height_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_fileupload_invalid_dimension')
                    ]
                ],
                'sortOrder' => 84
            ],
            'fileTypes' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_file_types'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_file_types_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 85
            ],
            'mimeTypes' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_mime_types'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_mime_types_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 86
            ],
            'useCaption' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_use_caption'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_use_caption_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'checkbox',
                'default' => true,
                'sortOrder' => 87
            ],
            'thumbOptions' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_options'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_options_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'type' => 'object',
                'properties' => [
                    [
                        'property' => 'mode',
                        'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_mode'),
                        'type' => 'dropdown',
                        'default' => 'crop',
                        'options' => [
                            'auto' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_auto'),
                            'exact' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_exact'),
                            'portrait' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_portrait'),
                            'landscape' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_landscape'),
                            'crop' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_crop')
                        ]
                    ],
                    [
                        'property' => 'extension',
                        'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_extension'),
                        'type' => 'dropdown',
                        'default' => 'auto',
                        'options' => [
                            'auto' => Lang::get('rainlab.builder::lang.form.property_fileupload_thumb_auto'),
                            'jpg' => 'jpg',
                            'gif' => 'gif',
                            'png' => 'png'
                        ]
                    ]
                ],
                'sortOrder' => 88
            ]
        ];

        $this->controlLibrary->registerControl(
            'fileupload',
            'rainlab.builder::lang.form.control_fileupload',
            'rainlab.builder::lang.form.control_fileupload_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-upload',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerRecordFinderWidget()
    {
        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'disabled'
        ];

        $properties = [
            'nameFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_name_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_name_from_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_recordfinder'),
                'type' => 'string',
                'default' => 'name',
                'sortOrder' => 81
            ],
            'descriptionFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_description_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_description_from_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_recordfinder'),
                'type' => 'string',
                'default' => 'description',
                'sortOrder' => 82
            ],
            'prompt' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_recordfinder_prompt'),
                'description' => Lang::get('rainlab.builder::lang.form.property_recordfinder_prompt_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_recordfinder'),
                'type' => 'builderLocalization',
                'ignoreIfEmpty' => true,
                'sortOrder' => 83
            ],
            'list' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_recordfinder_list'),
                'description' => Lang::get('rainlab.builder::lang.form.property_recordfinder_list_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_recordfinder'),
                'type' => 'autocomplete',
                'fillFrom' => 'plugin-lists',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_recordfinder_list_required'),
                    ]
                ],
                'sortOrder' => 83
            ]
        ];

        $this->controlLibrary->registerControl(
            'recordfinder',
            'rainlab.builder::lang.form.control_recordfinder',
            'rainlab.builder::lang.form.control_recordfinder_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-search',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerMediaFinderWidget()
    {
        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'disabled'
        ];

        $properties = [
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_mediafinder_mode'),
                'type' => 'dropdown',
                'default' => 'file',
                'options' => [
                    'file' => Lang::get('rainlab.builder::lang.form.property_mediafinder_mode_file'),
                    'image' => Lang::get('rainlab.builder::lang.form.property_mediafinder_mode_image')
                ],
                'sortOrder' => 81
            ],
            'prompt' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_mediafinder_prompt'),
                'description' => Lang::get('rainlab.builder::lang.form.property_mediafinder_prompt_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string',
                'sortOrder' => 82
            ],
            'imageWidth' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_image_width'),
                'description' => Lang::get('rainlab.builder::lang.form.property_mediafinder_image_width_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_fileupload_invalid_dimension')
                    ]
                ],
                'sortOrder' => 83
            ],
            'imageHeight' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_image_height'),
                'description' => Lang::get('rainlab.builder::lang.form.property_mediafinder_image_height_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_fileupload_invalid_dimension')
                    ]
                ],
                'sortOrder' => 84
            ],
        ];

        $this->controlLibrary->registerControl(
            'mediafinder',
            'rainlab.builder::lang.form.control_mediafinder',
            'rainlab.builder::lang.form.control_mediafinder_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-picture-o',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerRelationWidget()
    {
        $ignoreProperties = [
            'stretch',
            'default',
            'placeholder',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'trigger',
            'disabled'
        ];

        $properties = [
            'nameFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_name_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_name_from_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_relation'),
                'type' => 'string',
                'default' => 'name',
                'sortOrder' => 81
            ],
            'descriptionFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_description_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_description_from_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_relation'),
                'type' => 'string',
                'default' => 'description',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'emptyOption' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_relation_prompt'),
                'description' => Lang::get('rainlab.builder::lang.form.property_relation_prompt_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_relation'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 83
            ],
            'select' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_relation_select'),
                'description' => Lang::get('rainlab.builder::lang.form.property_relation_select_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_relation'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 84
            ],
            'scope' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_relation_scope'),
                'description' => Lang::get('rainlab.builder::lang.form.property_relation_scope_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_relation'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 85
            ]
        ];

        $this->controlLibrary->registerControl(
            'relation',
            'rainlab.builder::lang.form.control_relation',
            'rainlab.builder::lang.form.control_relation_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-code-fork',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }
}
