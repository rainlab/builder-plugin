<?php namespace RainLab\Builder\Classes;

use Lang;

/**
 * StandardBlueprintsRegistry
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class StandardBlueprintsRegistry
{
    protected $blueprintLibrary;

    public function __construct($blueprintLibrary)
    {
        $this->blueprintLibrary = $blueprintLibrary;

        $this->registerBlueprints();
    }

    protected function registerBlueprints()
    {
        $this->registerEntryBlueprint();
        $this->registerGlobalBlueprint();
    }

    protected function registerEntryBlueprint()
    {
        $properties = [
            'name' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_name'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_name_description'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_name_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_model_class_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_model_class_placeholder'),
                'type' => 'dropdown',
                'fillFrom' => 'model-classes',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_model_class_required')
                    ]
                ],
            ],
            'form' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_file'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_file_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_placeholder'),
                'type' => 'autocomplete',
                'fillFrom' => 'model-forms',
                'subtypeFrom' => 'modelClass',
                'depends' => ['modelClass'],
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_file_required')
                    ]
                ],
            ],
            'defaultRedirect' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_default_redirect'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_default_redirect_description'),
                'type' => 'autocomplete',
                'fillFrom' => 'controller-urls',
                'ignoreIfEmpty' => true
            ],
            'create' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_create'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'type' => 'builderLocalization',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_page_title'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirect',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirectClose',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect_close'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect_close_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'flashSave',
                        'type' => 'builderLocalization',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_flash_save'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_flash_save_description'),
                    ]
                ]
            ],
            'update' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_update'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'type' => 'builderLocalization',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_page_title'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirect',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirectClose',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect_close'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_redirect_close_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'flashSave',
                        'type' => 'builderLocalization',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_flash_save'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_flash_save_description'),
                    ],
                    [
                        'property' => 'flashDelete',
                        'type' => 'builderLocalization',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_flash_delete'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_flash_delete_description'),
                    ]
                ]
            ],
            'preview' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_preview'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'type' => 'builderLocalization',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_page_title'),
                        'ignoreIfEmpty' => true
                    ]
                ]
            ]
        ];

        $templates = [
            '$/rainlab/builder/classes/standardblueprintsregistry/formcontroller/templates/create.htm.tpl',
            '$/rainlab/builder/classes/standardblueprintsregistry/formcontroller/templates/update.htm.tpl',
            '$/rainlab/builder/classes/standardblueprintsregistry/formcontroller/templates/preview.htm.tpl'
        ];

        $this->blueprintLibrary->registerBlueprint(
            'Tailor\Classes\Blueprint\EntryBlueprint',
            'Entry Blueprint',
            'rainlab.builder::lang.controller.blueprint_form_controller_description',
            $properties,
            'formConfig',
            null,
            'config_form.yaml',
            $templates
        );
    }

    protected function registerGlobalBlueprint()
    {
        $properties = [
            'title' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_title'),
                'type' => 'builderLocalization',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_title_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_model_class_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_model_placeholder'),
                'type' => 'dropdown',
                'fillFrom' => 'model-classes',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_model_class_required')
                    ]
                ],
            ],
            'list' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_file'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_placeholder'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_file_description'),
                'type' => 'autocomplete',
                'fillFrom' => 'model-lists',
                'subtypeFrom' => 'modelClass',
                'depends' => ['modelClass'],
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_file_required')
                    ]
                ],
            ],
            'recordUrl' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_record_url'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_record_url_description'),
                'ignoreIfEmpty' => true,
                'type' => 'autocomplete',
                'fillFrom' => 'controller-urls',
            ],
            'noRecordsMessage' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_no_records_message'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_no_records_message_description'),
                'ignoreIfEmpty' => true,
                'type' => 'builderLocalization',
            ],
            'recordsPerPage' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_recs_per_page'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_recs_per_page_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string',
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_recs_per_page_regex')
                    ]
                ],
            ],
            'showSetup' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_show_setup'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'showCheckboxes' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_show_checkboxes'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'showSorting' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_show_sorting'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => false,
                'default' => true,
                'ignoreIfDefault' => true,
            ],
            'defaultSort' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_default_sort'),
                'ignoreIfEmpty' => true,
                'type' => 'object',
                'ignoreIfPropertyEmpty' => 'column',
                'properties' => [
                    [
                        'property' => 'column',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_ds_column'),
                        'type' => 'autocomplete',
                        'fillFrom' => 'model-columns',
                        'subtypeFrom' => 'modelClass',
                        'depends' => ['modelClass']
                    ],
                    [
                        'property' => 'direction',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_ds_direction'),
                        'type' => 'dropdown',
                        'options' => [
                            'asc' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_ds_asc'),
                            'desc' => Lang::get('rainlab.builder::lang.controller.property_blueprint_form_ds_desc'),
                        ],
                    ]
                ]
            ],
            'toolbar' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_toolbar'),
                'type' => 'object',
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'buttons',
                        'type' => 'string',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_toolbar_buttons'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_toolbar_buttons_description'),
                    ],
                    [
                        'property' => 'search',
                        'type' => 'object',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_search'),
                        'properties' => [
                            [
                                'property' => 'prompt',
                                'type' => 'builderLocalization',
                                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_search_prompt'),
                            ]
                        ]
                    ]
                ]
            ],
            'recordOnClick' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_onclick'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_onclick_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string'
            ],
            'showTree' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_show_tree'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_show_tree_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true
            ],
            'treeExpanded' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_tree_expanded'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_tree_expanded_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true
            ],
            'filter' => [
                'type' => 'string', // Should be configurable in place later
                'title' => Lang::get('rainlab.builder::lang.controller.property_blueprint_list_filter'),
                'ignoreIfEmpty' => true
            ]
        ];

        $templates = [
            '$/rainlab/builder/classes/standardblueprintsregistry/listcontroller/templates/index.htm.tpl',
            '$/rainlab/builder/classes/standardblueprintsregistry/listcontroller/templates/_list_toolbar.htm.tpl'
        ];

        $this->blueprintLibrary->registerBlueprint(
            'Tailor\Classes\Blueprint\GlobalBlueprint',
            'Global Blueprint',
            'rainlab.builder::lang.controller.blueprint_list_controller_description',
            $properties,
            'listConfig',
            null,
            'config_list.yaml',
            $templates
        );
    }
}
