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
        $this->registerCodeEditorWidget();
        $this->registerColorPickerWidget();
        $this->registerDatepickerWidget();
        $this->registerRichEditorWidget();
        $this->registerMarkdownWidget();
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
        $properties = $this->getFieldSizeProperties();

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
                'ignoreIfEmpty' => true,
                'sortOrder' => 51
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
                ],
                'sortOrder' => 51
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
                ],
                'sortOrder' => 51
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
                'ignoreIfEmpty' => true,
                'sortOrder' => 51
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
                'ignoreIfEmpty' => true,
                'sortOrder' => 51
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
                'sortOrder' => 51
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
                'sortOrder' => 51
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
                'sortOrder' => 52
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
                'sortOrder' => 53
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
                'sortOrder' => 54
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
                'sortOrder' => 55
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
                'sortOrder' => 56
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
                'sortOrder' => 57
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
                'sortOrder' => 58
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
                'sortOrder' => 59
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
                'sortOrder' => 60
            ],
            'readOnly' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_readonly'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_code_editor'),
                'default' => 0,
                'ignoreIfEmpty' => true,
                'type' => 'checkbox'
            ]
        ]);

        $this->controlLibrary->registerControl('codeeditor', 
            'rainlab.builder::lang.form.control_codeeditor',
            null,
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
                'sortOrder' => 51
            ]
        ];

        $this->controlLibrary->registerControl('colorpicker', 
            'rainlab.builder::lang.form.control_colorpicker',
            null,
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
                'sortOrder' => 51
            ],
            'minDate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_min_date'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_min_date_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_datepicker_date_invalid_format')
                    ]
                ],
                'sortOrder' => 52
            ],
            'maxDate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_max_date'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_max_date_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]{4}-[0-9]{2}-[0-9]{2}$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_datepicker_date_invalid_format')
                    ]
                ],
                'sortOrder' => 53
            ]
        ];

        $this->controlLibrary->registerControl('datepicker', 
            'rainlab.builder::lang.form.control_datepicker',
            null,
            ControlLibrary::GROUP_WIDGETS,
            'icon-calendar',
            $this->controlLibrary->getStandardProperties($ignoreProperties, $properties),
            null
        );
    }

    protected function registerRichEditorWidget()
    {
        $properties = $this->getFieldSizeProperties();

        $this->controlLibrary->registerControl('richeditor', 
            'rainlab.builder::lang.form.control_richeditor',
            null,
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
                'sortOrder' => 52
            ]
        ]);

        $this->controlLibrary->registerControl('markdown', 
            'rainlab.builder::lang.form.control_markdown',
            null,
            ControlLibrary::GROUP_WIDGETS,
            'icon-columns',
            $this->controlLibrary->getStandardProperties([], $properties),
            null
        );
    }
}