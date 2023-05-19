<?php namespace RainLab\Builder\Classes\StandardControlsRegistry;

use Lang;
use RainLab\Builder\Classes\ControlLibrary;

/**
 * HasFormWidgets
 */
trait HasFormWidgets
{
    /**
     * registerCodeEditorWidget
     */
    protected function registerCodeEditorWidget()
    {
        $excludeProperties = [
            'placeholder',
            'default',
            'defaultFrom',
            'dependsOn',
            'trigger',
            'preset',
            'attributes'
        ];

        $properties = $this->getFieldSizeProperties() + [
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
        ];

        $this->controlLibrary->registerControl(
            'codeeditor',
            'rainlab.builder::lang.form.control_codeeditor',
            'rainlab.builder::lang.form.control_codeeditor_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-code',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerColorPickerWidget
     */
    protected function registerColorPickerWidget()
    {
        $excludeProperties = [
            'stretch'
        ];

        $properties = [
            'availableColors' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_available_colors'),
                'description' => Lang::get('rainlab.builder::lang.form.property_available_colors_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_colorpicker'),
                'type' => 'stringList',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
            'allowEmpty' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_allow_empty'),
                'description' => Lang::get('rainlab.builder::lang.form.property_allow_empty_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_colorpicker'),
                'type' => 'checkbox',
                'default' => true,
                'ignoreIfEmpty' => true,
            ],
            'allowCustom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_allow_custom'),
                'description' => Lang::get('rainlab.builder::lang.form.property_allow_custom_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_colorpicker'),
                'type' => 'checkbox',
                'default' => true,
                'ignoreIfEmpty' => true,
            ],
            'showAlpha' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_show_alpha'),
                'description' => Lang::get('rainlab.builder::lang.form.property_show_alpha_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_colorpicker'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'showInput' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_show_input'),
                'description' => Lang::get('rainlab.builder::lang.form.property_show_input_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_colorpicker'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
        ];

        $this->controlLibrary->registerControl(
            'colorpicker',
            'rainlab.builder::lang.form.control_colorpicker',
            'rainlab.builder::lang.form.control_colorpicker_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-eyedropper',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerDataTableWidget
     */
    protected function registerDataTableWidget()
    {
        $excludeProperties = [
            'stretch'
        ];

        $properties = [
            'oc.columns' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_columns'),
                'description' => Lang::get('rainlab.builder::lang.form.property_columns_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datatable'),
                'type' => 'objectList',
                'ignoreIfEmpty' => true,
                'titleProperty' => 'title',
                'itemProperties' => [
                    [
                        'property' => 'type',
                        'title' => Lang::get('rainlab.builder::lang.form.property_datatable_type'),
                        'type' => 'dropdown',
                        'default' => 'string',
                        'options' => [
                            'string' => "String",
                            'checkbox' => "Checkbox",
                            'dropdown' => "Dropdown",
                            'autocomplete' => "Autocomplete",
                        ],
                    ],
                    [
                        'property' => 'code',
                        'title' => Lang::get('rainlab.builder::lang.form.property_datatable_code'),
                        'type' => 'string',
                        'validation' => [
                            'required' => [
                                'message' => Lang::get('rainlab.builder::lang.form.property_datatable_code_regex'),
                            ]
                        ],
                    ],
                    [
                        'property' => 'title',
                        'title' => Lang::get('rainlab.builder::lang.form.property_datatable_title'),
                        'type' => 'string'
                    ],
                    [
                        'property' => 'options',
                        'title' => Lang::get('rainlab.builder::lang.form.property_options'),
                        'type' => 'dictionary',
                        'ignoreIfEmpty' => true,
                    ],
                    [
                        'property' => 'width',
                        'title' => Lang::get('rainlab.builder::lang.form.property_datatable_width'),
                        'type' => 'string',
                        'validation' => [
                            'regex' => [
                                'pattern' => '^[0-9]+$',
                                'message' => Lang::get('rainlab.builder::lang.form.property_datatable_width_regex')
                            ]
                        ],
                    ]
                ],
                'sortOrder' => 81
            ],
            'adding' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datatable_adding'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datatable_adding_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datatable'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'deleting' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datatable_deleting'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datatable_deleting_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datatable'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'searching' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datatable_searching'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datatable_searching_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datatable'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
        ];

        $this->controlLibrary->registerControl(
            'datatable',
            'rainlab.builder::lang.form.control_datatable',
            'rainlab.builder::lang.form.control_datatable_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-table',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerDatepickerWidget
     */
    protected function registerDatepickerWidget()
    {
        $excludeProperties = [
            'stretch'
        ];

        $properties = [
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'dropdown',
                'default' => 'datetime',
                'options' => [
                    'date' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode_date'),
                    'datetime' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode_datetime'),
                    'time' => Lang::get('rainlab.builder::lang.form.property_datepicker_mode_time')
                ],
                'sortOrder' => 81
            ],
            'format' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_format'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_format_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'minDate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_min_date'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_min_date_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 83
            ],
            'maxDate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_max_date'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_max_date_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 84
            ],
            'yearRange' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_range'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_range_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^([0-9]+|\[[0-9]{4},[0-9]{4}\])$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_datepicker_year_range_invalid_format')
                    ]
                ],
                'sortOrder' => 85
            ],
            'firstDay' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_first_day'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_first_day_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'string',
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_datepicker_first_day_regex')
                    ]
                ],
                'ignoreIfEmpty' => true,
                'sortOrder' => 86
            ],
            'twelveHour' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_twelve_hour'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_twelve_hour_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
                'sortOrder' => 87
            ],
            'showWeekNumber' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_datepicker_show_week_number'),
                'description' => Lang::get('rainlab.builder::lang.form.property_datepicker_show_week_number_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_datepicker'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
                'sortOrder' => 88
            ],
        ];

        $this->controlLibrary->registerControl(
            'datepicker',
            'rainlab.builder::lang.form.control_datepicker',
            'rainlab.builder::lang.form.control_datepicker_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-calendar',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerFileUploadWidget
     */
    protected function registerFileUploadWidget()
    {
        $excludeProperties = [
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
            'thumbOptions' => $this->getFieldThumbOptionsProperties()['thumbOptions'],
            'maxFilesize' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_maxfilesize'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_maxfilesize_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'sortOrder' => 89,
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9\.]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_fileupload_invalid_maxfilesize')
                    ]
                ],
            ],
            'maxFiles' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_fileupload_maxfiles'),
                'description' => Lang::get('rainlab.builder::lang.form.property_fileupload_maxfiles_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_fileupload'),
                'sortOrder' => 90,
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.form.property_fileupload_invalid_maxfiles')
                    ]
                ],
            ]
        ];

        $this->controlLibrary->registerControl(
            'fileupload',
            'rainlab.builder::lang.form.control_fileupload',
            'rainlab.builder::lang.form.control_fileupload_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-upload',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerMarkdownWidget
     */
    protected function registerMarkdownWidget()
    {
        $properties = $this->getFieldSizeProperties() + [
            'sideBySide' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_side_by_side'),
                'description' => Lang::get('rainlab.builder::lang.form.property_side_by_side_description'),
                'type' => 'checkbox',
                'sortOrder' => 81
            ]
        ];

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

    /**
     * registerMediaFinderWidget
     */
    protected function registerMediaFinderWidget()
    {
        $excludeProperties = [
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
                'sortOrder' => 82
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
                'sortOrder' => 83
            ],
            'maxItems' => $this->getFieldMaxItemsProperties()['maxItems'],
            'thumbOptions' => $this->getFieldThumbOptionsProperties()['thumbOptions'],
        ];

        $this->controlLibrary->registerControl(
            'mediafinder',
            'rainlab.builder::lang.form.control_mediafinder',
            'rainlab.builder::lang.form.control_mediafinder_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-picture-o',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerNestedFormWidget
     */
    protected function registerNestedFormWidget()
    {
        $properties = [
            'form' => [
                'type' => 'control-container'
            ],
            'showPanel' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_nestedform_show_panel'),
                'description' => Lang::get('rainlab.builder::lang.form.property_nestedform_show_panel_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
                'sortOrder' => 87,
            ],
            'defaultCreate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_nestedform_default_create'),
                'description' => Lang::get('rainlab.builder::lang.form.property_nestedform_default_create_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
                'sortOrder' => 88,
            ],
        ];

        $excludeProperties = [
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
            'nestedform',
            'rainlab.builder::lang.form.control_nestedform',
            'rainlab.builder::lang.form.control_nestedform_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-object-group',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerRecordFinderWidget
     */
    protected function registerRecordFinderWidget()
    {
        $excludeProperties = [
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
            'title' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_recordfinder_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_recordfinder_title_description'),
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
            ],
            'scope' => $this->getFieldConditionsProperties()['scope'],
        ];

        $this->controlLibrary->registerControl(
            'recordfinder',
            'rainlab.builder::lang.form.control_recordfinder',
            'rainlab.builder::lang.form.control_recordfinder_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-search',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerRelationWidget
     */
    protected function registerRelationWidget()
    {
        $excludeProperties = [
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
            'scope' => $this->getFieldConditionsProperties()['scope'],
        ];

        $this->controlLibrary->registerControl(
            'relation',
            'rainlab.builder::lang.form.control_relation',
            'rainlab.builder::lang.form.control_relation_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-code-fork',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerRepeaterWidget
     */
    protected function registerRepeaterWidget()
    {
        $properties = [
            'prompt' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_prompt'),
                'description' => Lang::get('rainlab.builder::lang.form.property_prompt_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'default' => Lang::get('rainlab.builder::lang.form.property_prompt_default'),
                'sortOrder' => 81
            ],
            'titleFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_title_from'),
                'description' => Lang::get('rainlab.builder::lang.form.property_title_from_description'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 82
            ],
            'form' => [
                'type' => 'control-container'
            ],
            'minItems' => $this->getFieldMaxItemsProperties()['minItems'],
            'maxItems' => $this->getFieldMaxItemsProperties()['maxItems'],
            'displayMode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_display_mode'),
                'description' => Lang::get('rainlab.builder::lang.form.property_display_mode_description'),
                'type' => 'dropdown',
                'default' => 'accordion',
                'options' => [
                    'builder' => Lang::get('rainlab.builder::lang.form.display_mode_builder'),
                    'accordion' => Lang::get('rainlab.builder::lang.form.display_mode_accordion'),
                ],
                'sortOrder' => 85,
            ],
            // @todo this needs work, the control container doesn't support tabs
            // 'useTabs' => [
            //     'title' => "Use Tabs",
            //     'description' => "Shows tabs when enabled, allowing fields to specify a tab property.",
            //     'type' => 'checkbox',
            //     'ignoreIfEmpty' => true,
            //     'sortOrder' => 86,
            // ],
            'showReorder' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_repeater_show_reorder'),
                'description' => Lang::get('rainlab.builder::lang.form.property_repeater_show_reorder_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
                'sortOrder' => 87,
            ],
            'showDuplicate' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_repeater_show_duplicate'),
                'description' => Lang::get('rainlab.builder::lang.form.property_repeater_show_duplicate_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
                'sortOrder' => 88,
            ],
        ];

        $excludeProperties = [
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
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerRichEditorWidget
     */
    protected function registerRichEditorWidget()
    {
        $properties = $this->getFieldSizeProperties() + [
            'toolbarButtons' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_richeditor_toolbar_buttons'),
                'description' => Lang::get('rainlab.builder::lang.form.property_richeditor_toolbar_buttons_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_rich_editor'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 81
            ],
        ];

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

    /**
     * registerPageFinderWidget
     */
    protected function registerPageFinderWidget()
    {
        $properties = [
            'singleMode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_pagefinder_single_mode'),
                'description' => Lang::get('rainlab.builder::lang.form.property_pagefinder_single_mode_description'),
                'type' => 'checkbox',
                'sortOrder' => 81
            ]
        ];

        $this->controlLibrary->registerControl(
            'pagefinder',
            'rainlab.builder::lang.form.control_pagefinder',
            'rainlab.builder::lang.form.control_pagefinder_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-paperclip',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    /**
     * registerSensitiveWidget
     */
    protected function registerSensitiveWidget()
    {
        $properties = [
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_display_mode'),
                'description' => Lang::get('rainlab.builder::lang.form.property_display_mode_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_sensitive'),
                'type' => 'dropdown',
                'options' => [
                    'text' => "Text",
                    'textarea' => "Textarea",
                ],
                'ignoreIfDefault' => true,
                'default' => 'text',
                'sortOrder' => 83
            ],
            'allowCopy' => [
                'title' => Lang::get('rainlab.builder::lang.form.allow_copy'),
                'description' => Lang::get('rainlab.builder::lang.form.allow_copy_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_sensitive'),
                'type' => 'checkbox',
                'ignoreIfDefault' => true,
                'sortOrder' => 84,
                'default' => true
            ],
            'hiddenPlaceholder' => [
                'title' => Lang::get('rainlab.builder::lang.form.hidden_placeholder'),
                'description' => Lang::get('rainlab.builder::lang.form.hidden_placeholder_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_sensitive'),
                'type' => 'string',
                'default' => '__hidden__',
                'ignoreIfDefault' => true,
                'sortOrder' => 85
            ],
            'hideOnTabChange' => [
                'title' => Lang::get('rainlab.builder::lang.form.hide_on_tab_change'),
                'description' => Lang::get('rainlab.builder::lang.form.hide_on_tab_change_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_sensitive'),
                'type' => 'checkbox',
                'ignoreIfDefault' => true,
                'sortOrder' => 86,
                'default' => true
            ],
        ];

        $this->controlLibrary->registerControl(
            'sensitive',
            'rainlab.builder::lang.form.control_sensitive',
            'rainlab.builder::lang.form.control_sensitive_description',
            ControlLibrary::GROUP_WIDGETS,
            'icon-eye-slash',
            $this->controlLibrary->getStandardProperties(['stretch'], $properties),
            null
        );
    }

    /**
     * registerTagListWidget
     */
    protected function registerTagListWidget()
    {
        $excludeProperties = [
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
            'optionsMethod' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_options_method'),
                'description' => Lang::get('rainlab.builder::lang.form.property_options_method_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_taglist'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 86
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
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * getFieldThumbOptionsProperties
     */
    protected function getFieldThumbOptionsProperties(): array
    {
        return [
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
    }

    /**
     * getFieldMaxItemsProperties
     */
    protected function getFieldMaxItemsProperties(): array
    {
        return [
            'minItems' => [
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
            'maxItems' => [
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
        ];
    }

    /**
     * getFieldConditionsProperties
     */
    protected function getFieldConditionsProperties(): array
    {
        return [
            'scope' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_relation_scope'),
                'description' => Lang::get('rainlab.builder::lang.form.property_relation_scope_description'),
                'group' => Lang::get('rainlab.builder::lang.form.property_group_relation'),
                'type' => 'string',
                'ignoreIfEmpty' => true,
                'sortOrder' => 85
            ]
        ];
    }

    /**
     * getFieldSizeProperties
     */
    protected function getFieldSizeProperties(): array
    {
        return [
            'size' => [
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
}
