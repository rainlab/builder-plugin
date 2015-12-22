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
    }

    protected function registerFormBehavior()
    {
        $properties = [
            'name' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_name'),
                'description' => Lang::get('rainlab.builder::lang.form.property_behavior_form_name_description'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.property_behavior_form_name.property_behavior_form_name_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_model_class_description'),
                'type' => 'string', // Should be a dropdown 
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.property_behavior_form_name.property_behavior_form_model_class_required')
                    ]
                ],
            ],
            'form' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_file'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_file_description'),
                'type' => 'string', // Should be a dropdown dependent on the model class
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.property_behavior_form_name.property_behavior_form_file_required')
                    ]
                ],
            ],
            'defaultRedirect' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_default_redirect'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_default_redirect_description'),
                'type' => 'string', // Should be an autocomplete
                'ignoreIfEmpty' => true
            ],
            'create' => [
                'type' => 'object',
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_create'),
                'ignoreIfEmpty' => true,
                'properties' => [
                    [
                        'property' => 'title',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_page_title'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirect',
                        'type' => 'string', // Should be an autocomplete
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirectClose',
                        'type' => 'string', // Should be an autocomplete
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'flashSave',
                        'type' => 'string', // Should be a localization autocomplete
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
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_page_title'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirect',
                        'type' => 'string', // Should be an autocomplete
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'redirectClose',
                        'type' => 'string', // Should be an autocomplete
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_redirect_close_description'),
                        'ignoreIfEmpty' => true
                    ],
                    [
                        'property' => 'flashSave',
                        'type' => 'string', // Should be a localization autocomplete
                        'ignoreIfEmpty' => true,
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_save'),
                        'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_flash_save_description'),
                    ],
                    [
                        'property' => 'flashDelete',
                        'type' => 'string', // Should be a localization autocomplete
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
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_form_page_title'),
                        'ignoreIfEmpty' => true
                    ]
                ]
            ]
        ];

        $templates = [
            '~/plugins/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/create.htm.tpl',
            '~/plugins/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/update.htm.tpl',
            '~/plugins/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/preview.htm.tpl'
        ];

        $this->behaviorLibrary->registerBehavior(
            'Backend\Behaviors\FormController',
            'rainlab.builder::lang.controller.behavior_form_controller',
            'rainlab.builder::lang.controller.behavior_form_controller_description',
            $properties,
            'formConfig',
            null,
            '~/plugins/rainlab/builder/classes/standardbehaviorsregistry/formcontroller/templates/config_form.yaml.tpl',
            $templates);
    }

    protected function registerListBehavior()
    {
        $properties = [
            'title' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_title'),
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.property_behavior_form_name.property_behavior_list_title_required')
                    ]
                ],
            ],
            'modelClass' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_model_class'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_model_class_description'),
                'type' => 'string', // Should be a dropdown 
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.property_behavior_form_name.property_behavior_list_model_class_required')
                    ]
                ],
            ],
            'list' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_file'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_file_description'),
                'type' => 'string', // Should be a dropdown dependent on the model class
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.property_behavior_form_name.property_behavior_list_file_required')
                    ]
                ],
            ],
            'recordUrl' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_record_url'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_record_url_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string'
            ],
            'noRecordsMessage' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_no_records_message'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_no_records_message_description'),
                'ignoreIfEmpty' => true,
                'type' => 'string', // Should be a localization autocomplete
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
                'type' => 'checkbox'
            ],
            'showCheckboxes' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_checkboxes'),
                'type' => 'checkbox'
            ],
            'showSorting' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_show_sorting'),
                'type' => 'checkbox'
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
                        'type' => 'string'
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
                        'ignoreIfPropertyEmpty' => 'prompt',
                        'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_search'),
                        'properties' => [
                            [
                                'property' => 'prompt',
                                'type' => 'string',
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
                'type' => 'checkbox'
            ],
            'treeExpanded' => [
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_tree_expanded'),
                'description' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_tree_expanded_description'),
                'type' => 'checkbox'
            ],
            'filter' => [
                'type' => 'string', // Should be configurable in place later
                'title' => Lang::get('rainlab.builder::lang.controller.property_behavior_list_filter'),
                'ignoreIfEmpty' => true
            ]
        ];

        $templates = [
            '~/plugins/rainlab/builder/classes/standardbehaviorsregistry/listcontroller/templates/index.htm.tpl'
        ];

        $this->behaviorLibrary->registerBehavior(
            'Backend\Behaviors\ListController',
            'rainlab.builder::lang.controller.behavior_list_controller',
            'rainlab.builder::lang.controller.behavior_list_controller_description',
            $properties,
            'listConfig',
            null,
            '~/plugins/rainlab/builder/classes/standardbehaviorsregistry/listcontroller/templates/config_list.yaml.tpl',
            $templates);
    }
}