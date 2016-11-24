<?php namespace RainLab\Builder\Classes;

use Lang;

/**
 * Utility class for registering standard controller behaviors.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class StandardBehaviorsRegistry
{
    protected $behaviorLibrary;

    public function __construct($behaviorLibrary)
    {
        $this->behaviorLibrary = $behaviorLibrary;

        $this->registerBehaviors();
    }

    protected function registerBehaviors()
    {
        $this->registerListBehavior();
        $this->registerFormBehavior();
        $this->registerReorderBehavior();
    }

    protected function registerFormBehavior()
    {
        $properties = [
            'name' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_name'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_name_description'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_name_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_model_class_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_model_class_placeholder'),
                'type' => 'dropdown',
                'fillFrom' => 'model-classes',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_model_class_required')
                    ]
                ],
            ],
            'form' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_file'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_file_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_placeholder'),
                'type' => 'autocomplete',
                'fillFrom' => 'model-forms',
                'subtypeFrom' => 'modelClass',
                'depends' => ['modelClass'],
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_file_required')
                    ]
                ],
            ],
            'defaultRedirect' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_default_redirect'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_default_redirect_description'),
                'type' => 'autocomplete',
                'fillFrom' => 'controller-urls',
                'ignoreIfEmpty' => true
            ],
            'create' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_create'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'type' => 'builderLocalization',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_page_title'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirect',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirectClose',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'flashSave',
                        'type' => 'builderLocalization',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_save'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_save_description'),
                    ]
                ]
            ],
            'update' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_update'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'type' => 'builderLocalization',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_page_title'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirect',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirectClose',
                        'type' => 'autocomplete',
                        'fillFrom' => 'controller-urls',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'flashSave',
                        'type' => 'builderLocalization',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_save'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_save_description'),
                    ],
                    [
                        'property' => 'flashDelete',
                        'type' => 'builderLocalization',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_delete'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_delete_description'),
                    ]
                ]
            ],
            'preview' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_preview'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'type' => 'builderLocalization',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_page_title'),
                        'ignoreIfEmpty' => true
                    ]
                ]
            ]
        ];

        $templates = [
            '$/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/create.htm.tpl',
            '$/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/update.htm.tpl',
            '$/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/preview.htm.tpl'
        ];

        $this->behaviorLibrary->registerBehavior(
            'Backend\Behaviors\FormController',
            'rainlab.builder::lang.controller.behavior_form_controller',
            'rainlab.builder::lang.controller.behavior_form_controller_description',
            $properties,
            'formConfig',
            null,
            'config_form.yaml',
            $templates
        );
    }

    protected function registerListBehavior()
    {
        $properties = [
            'title' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_title'),
                'type' => 'builderLocalization',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_title_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_model_class_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_model_placeholder'),
                'type' => 'dropdown',
                'fillFrom' => 'model-classes',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_model_class_required')
                    ]
                ],
            ],
            'list' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_file'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_placeholder'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_file_description'),
                'type' => 'autocomplete',
                'fillFrom' => 'model-lists',
                'subtypeFrom' => 'modelClass',
                'depends' => ['modelClass'],
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_file_required')
                    ]
                ],
            ],
            'recordUrl' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_record_url'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_record_url_description'),
                'ignoreIfEmpty' => true,
                'type' => 'autocomplete',
                'fillFrom' => 'controller-urls',
            ],
            'noRecordsMessage' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_no_records_message'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_no_records_message_description'),
                'ignoreIfEmpty' => true,
                'type' => 'builderLocalization',
            ],
            'recordsPerPage' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_recs_per_page'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_recs_per_page_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string',
                'validation' => [
                    'regex' => [
                        'pattern' => '^[0-9]+$',
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_recs_per_page_regex')
                    ]
                ],
            ],
            'showSetup' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_setup'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'showCheckboxes' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_checkboxes'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'showSorting' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_sorting'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true,
            ],
            'defaultSort' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_default_sort'),
                'ignoreIfEmpty' => true,
                'type' => 'object',
                'ignoreIfPropertyEmpty' => 'column',
                'properties' => [
                    [
                        'property' => 'column',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_ds_column'),
                        'type' => 'autocomplete',
                        'fillFrom' => 'model-columns',
                        'subtypeFrom' => 'modelClass',
                        'depends' => ['modelClass']
                    ],
                    [
                        'property' => 'direction',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_ds_direction'),
                        'type' => 'dropdown',
                        'options' => [
                            'asc' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_ds_asc'),
                            'desc' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_ds_desc'),
                        ],
                    ]
                ]
            ],
            'toolbar' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_toolbar'),
                'type' => 'object',
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'buttons',
                        'type' => 'string',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_toolbar_buttons'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_toolbar_buttons_description'),
                    ],
                    [
                        'property' => 'search',
                        'type' => 'object',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_search'),
                        'properties' => [
                            [
                                'property' => 'prompt',
                                'type' => 'builderLocalization',
                                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_search_prompt'),
                            ]
                        ]
                    ]
                ]
            ],
            'recordOnClick' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_onclick'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_onclick_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string'
            ],
            'showTree' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_tree'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_tree_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true
            ],
            'treeExpanded' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_tree_expanded'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_tree_expanded_description'),
                'type' => 'checkbox',
                'ignoreIfEmpty' => true
            ],
            'filter' => [
                'type' => 'string', // Should be configurable in place later
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_filter'),
                'ignoreIfEmpty' => true
            ]
        ];

        $templates = [
            '$/rainlab/builder/classes/standardbehaviorsregistry/listcontroller/templates/index.htm.tpl',
            '$/rainlab/builder/classes/standardbehaviorsregistry/listcontroller/templates/_list_toolbar.htm.tpl'
        ];

        $this->behaviorLibrary->registerBehavior(
            'Backend\Behaviors\ListController',
            'rainlab.builder::lang.controller.behavior_list_controller',
            'rainlab.builder::lang.controller.behavior_list_controller_description',
            $properties,
            'listConfig',
            null,
            'config_list.yaml',
            $templates
        );
    }

    protected function registerReorderBehavior()
    {
        $properties = [
            'title' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_title'),
                'type' => 'builderLocalization',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_title_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_model_class_description'),
                'placeholder' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_model_placeholder'),
                'type' => 'dropdown',
                'fillFrom' => 'model-classes',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_model_class_required')
                    ]
                ],
            ],
            'nameFrom' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_name_from'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_name_from_description'),
                'type' => 'autocomplete',
                'fillFrom' => 'model-columns',
                'subtypeFrom' => 'modelClass',
                'depends' => ['modelClass'],
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_name_from_required')
                    ]
                ],
            ],
            'toolbar' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_toolbar'),
                'type' => 'object',
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'buttons',
                        'type' => 'string',
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_toolbar_buttons'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_reorder_toolbar_buttons_description'),
                    ]
                ]
            ],
        ];

        $templates = [
            '$/rainlab/builder/classes/standardbehaviorsregistry/reordercontroller/templates/reorder.htm.tpl',
            '$/rainlab/builder/classes/standardbehaviorsregistry/reordercontroller/templates/_reorder_toolbar.htm.tpl'
        ];

        $this->behaviorLibrary->registerBehavior(
            'Backend\Behaviors\ReorderController',
            'rainlab.builder::lang.controller.behavior_reorder_controller',
            'rainlab.builder::lang.controller.behavior_reorder_controller_description',
            $properties,
            'reorderConfig',
            null,
            'config_reorder.yaml',
            $templates
        );
    }
}