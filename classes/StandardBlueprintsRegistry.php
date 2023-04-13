<?php namespace RainLab\Builder\Classes;

/**
 * StandardBlueprintsRegistry
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class StandardBlueprintsRegistry
{
    /**
     * @var TailorBlueprintLibrary blueprintLibrary
     */
    protected $blueprintLibrary;

    /**
     * __construct
     */
    public function __construct($blueprintLibrary)
    {
        $this->blueprintLibrary = $blueprintLibrary;

        $this->registerBlueprints();
    }

    /**
     * registerBlueprints
     */
    protected function registerBlueprints()
    {
        $this->registerEntryBlueprint();
        $this->registerGlobalBlueprint();
    }

    /**
     * registerEntryBlueprint
     */
    protected function registerEntryBlueprint()
    {
        $properties = [
            'name' => [
                'title' => "Name",
                'description' => "The name to use for this blueprint in the user interface",
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => "A name is required"
                    ]
                ],
            ],
            'controllerClass' => [
                'title' => "Controller Class",
                'description' => "Controller name defines the class name and URL of the controller's back-end pages. Standard PHP variable naming conventions apply. The first symbol should be a capital Latin letter. Examples: Categories, Posts, Products.",
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => "A singular name is required"
                    ]
                ],
            ],
            'modelClass' => [
                'title' => "Model Class",
                'description' => "Model name defines the class name of the model. Standard PHP variable naming conventions apply. The first symbol should be a capital Latin letter. Examples: Category, Post, Product.",
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => "A singular name is required"
                    ]
                ],
            ],
        ];

        $templates = [];

        $this->blueprintLibrary->registerBlueprint(
            \Tailor\Classes\Blueprint\EntryBlueprint::class,
            'Entry Blueprint',
            'The standard content structure that supports drafts.',
            $properties,
            'formConfig',
            null,
            'config_form.yaml',
            $templates
        );

        $this->blueprintLibrary->registerBlueprint(
            \Tailor\Classes\Blueprint\StreamBlueprint::class,
            'Stream Blueprint',
            'A stream of time stamped entries.',
            $properties,
            'formConfig',
            null,
            'config_form.yaml',
            $templates
        );

        $this->blueprintLibrary->registerBlueprint(
            \Tailor\Classes\Blueprint\SingleBlueprint::class,
            'Single Blueprint',
            'A single entry with dedicated fields.',
            $properties,
            'formConfig',
            null,
            'config_form.yaml',
            $templates
        );

        $this->blueprintLibrary->registerBlueprint(
            \Tailor\Classes\Blueprint\StructureBlueprint::class,
            'Structure Blueprint',
            'A defined structure of entries.',
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
            'name' => [
                'title' => "Singular Name",
                'description' => "A singular reference to the blueprint object",
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => "A singular name is required"
                    ]
                ],
            ],
            'controllerClass' => [
                'title' => "Controller Class",
                'description' => "Controller name defines the class name and URL of the controller's back-end pages. Standard PHP variable naming conventions apply. The first symbol should be a capital Latin letter. Examples: Categories, Posts, Products.",
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => "A singular name is required"
                    ]
                ],
            ],
            'modelClass' => [
                'title' => "Model Class",
                'description' => "Model name defines the class name of the model. Standard PHP variable naming conventions apply. The first symbol should be a capital Latin letter. Examples: Category, Post, Product.",
                'type' => 'string',
                'validation' => [
                    'required' => [
                        'message' => "A singular name is required"
                    ]
                ],
            ],
        ];

        $templates = [];

        $this->blueprintLibrary->registerBlueprint(
            \Tailor\Classes\Blueprint\GlobalBlueprint::class,
            'Global Blueprint',
            'A single record in the database and is often used for settings and configuration.',
            $properties,
            'listConfig',
            null,
            'config_list.yaml',
            $templates
        );
    }
}
