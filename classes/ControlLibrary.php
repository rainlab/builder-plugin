<?php namespace RainLab\Builder\Classes;

use Event;
use Lang;

/**
 * Manages Builder form control library.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ControlLibrary
{
    use \October\Rain\Support\Traits\Singleton;

    const GROUP_STANDARD = 0;
    const GROUP_WIDGETS = 1;
    const DEFAULT_DESIGN_TIME_PROVIDER = 'RainLab\Builder\Widgets\DefaultControlDesignTimeProvider';

    protected $controls = [];

    protected $groupedControls = null;

    /**
     * Returns a list of all known form controls grouped by control groups.
     * @param boolean $returnGrouped Indicates whether controls should be grouped in the result.
     * @return array
     */
    public function listControls($returnGrouped = true)
    {
        if ($this->groupedControls !== null) {
            return $returnGrouped ? $this->groupedControls : $this->controls;
        }

        $this->groupedControls = [
            $this->resolveControlGroupName(self::GROUP_STANDARD) => [],
            $this->resolveControlGroupName(self::GROUP_WIDGETS) => []
        ];

        Event::fire('pages.builder.registerControls', [$this]);
        
        foreach ($this->controls as $controlType=>$controlInfo) {
            $controlGroup = $this->resolveControlGroupName($controlInfo['group']);

            if (!array_key_exists($controlGroup, $this->groupedControls)) {
                $this->groupedControls[$controlGroup] = [];
            }

            $this->groupedControls[$controlGroup][$controlType] = $controlInfo;
        }

        return $returnGrouped ? $this->groupedControls : $this->controls;
    }

    /**
     * Returns information about a control by its code.
     * @param string $code Specifies the control code.
     * @return array Returns an associative array or null if the control is not registered.
     */
    public function getControlInfo($code)
    {
        $controls = $this->listControls(false);

        if (array_key_exists($code, $controls)) {
            return $controls[$code];
        }

        return [
            'properties' => [],
            'designTimeProvider' => self::DEFAULT_DESIGN_TIME_PROVIDER,
            'name' => $code,
            'description' => null,
            'unknownControl' => true
        ];
    }

    /**
     * Registers a control.
     * @param string $code Specifies the control code, for example "codeeditor".
     * @param string $name Specifies the control name, for example "Code editor".
     * @param string $description Specifies the control descritpion, can be empty.
     * @param string|integer $controlGroup Specifies the control group.
     * Control groups are used to create tabs in the Control Palette in Form Builder.
     * The group could one of the ControlLibrary::GROUP_ constants or a string.
     * @param string $icon Specifies the control icon for the Control Palette.
     * @see http://octobercms.com/docs/ui/icon
     * @param array $properties Specifies the control properties.
     * The property definitions should be compatible with Inspector properties, similarly
     * to the Component properties: http://octobercms.com/docs/plugin/components#component-properties
     * Use the getStandardProperties() of the ControlLibrary to get the standard control properties.
     * @param string $designTimeProviderClass Specifies the control design-time provider class name.
     * The class should extend RainLab\Builder\Classes\ControlDesignTimeProviderBase. If the class is not provided,
     * the default control design and design settings will be used.
     */
    public function registerControl($code, $name, $description, $controlGroup, $icon, $properties, $designTimeProviderClass)
    {
        if (!$designTimeProviderClass) {
            $designTimeProviderClass = self::DEFAULT_DESIGN_TIME_PROVIDER;
        }

        $this->controls[$code] = [
            'group' => $controlGroup,
            'name' => $name,
            'description' => $description,
            'icon' => $icon,
            'properties' => $properties,
            'designTimeProvider' => $designTimeProviderClass
        ];
    }

    public function getStandardProperties($excludeProperties = [], $addProperties = [])
    {
        $result = [
            'label' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_label_title'),
                'type' => 'builderLocalization',
                'validation' => [
                    'required' => [
                        'message' => Lang::get('rainlab.builder::lang.form.property_label_required')
                    ]
                ]
            ],
            'oc.comment' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_comment_title'),
                'type' => 'builderLocalization',
            ],
            'oc.commentPosition' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_comment_position'),
                'type' => 'dropdown',
                'options' => [
                    'above' => Lang::get('rainlab.builder::lang.form.property_comment_position_above'),
                    'below' => Lang::get('rainlab.builder::lang.form.property_comment_position_below')
                ],
                'ignoreIfEmpty' => true,
            ],
            'span' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_span_title'),
                'type' => 'dropdown',
                'default' => 'full',
                'options' => [
                    'left' => Lang::get('rainlab.builder::lang.form.span_left'),
                    'right' => Lang::get('rainlab.builder::lang.form.span_right'),
                    'full' => Lang::get('rainlab.builder::lang.form.span_full'),
                    'auto' => Lang::get('rainlab.builder::lang.form.span_auto')
                ]
            ],
            'placeholder' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_placeholder_title'),
                'type' => 'builderLocalization',
            ],
            'default' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_default_title'),
                'type' => 'builderLocalization',
            ],
            'cssClass' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_css_class_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_css_class_description'),
                'type' => 'string'
            ],
            'disabled' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_disabled_title'),
                'type' => 'checkbox'
            ],
            'hidden' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_hidden_title'),
                'type' => 'checkbox'
            ],
            'required' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_required_title'),
                'type' => 'checkbox'
            ],
            'stretch' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_stretch_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_stretch_description'),
                'type' => 'checkbox'
            ],
            'context' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_context_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_context_description'),
                'type' => 'set',
                'items' => [
                    'create' => Lang::get('rainlab.builder::lang.form.property_context_create'),
                    'update' => Lang::get('rainlab.builder::lang.form.property_context_update'),
                    'preview' => Lang::get('rainlab.builder::lang.form.property_context_preview')
                ],
                'default' => ['create', 'update', 'preview'],
                'ignoreIfDefault' => true
            ]
        ];

        $result = array_merge($result, $addProperties);

        $advancedProperties = [
            'defaultFrom' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_default_from_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_default_from_description'),
                'type' => 'dropdown',
                'group' => Lang::get('rainlab.builder::lang.form.property_group_advanced'),
                'ignoreIfEmpty' => true,
                'fillFrom' => 'form-controls'
            ],
            'dependsOn' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_dependson_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_dependson_description'),
                'type' => 'stringList',
                'group' => Lang::get('rainlab.builder::lang.form.property_group_advanced'),
            ],
            'trigger' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_trigger_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_trigger_description'),
                'type' => 'object',
                'group' => Lang::get('rainlab.builder::lang.form.property_group_advanced'),
                'ignoreIfPropertyEmpty' => 'field',
                'properties' => [
                    [
                        'property' => 'action',
                        'title' => Lang::get('rainlab.builder::lang.form.property_trigger_action'),
                        'type' => 'dropdown',
                        'options' => [
                            'show' => Lang::get('rainlab.builder::lang.form.property_trigger_show'),
                            'hide' => Lang::get('rainlab.builder::lang.form.property_trigger_hide'),
                            'enable' => Lang::get('rainlab.builder::lang.form.property_trigger_enable'),
                            'disable' => Lang::get('rainlab.builder::lang.form.property_trigger_disable'),
                            'empty' => Lang::get('rainlab.builder::lang.form.property_trigger_empty')
                        ]
                    ],
                    [
                        'property' => 'field',
                        'title' => Lang::get('rainlab.builder::lang.form.property_trigger_field'),
                        'description' => Lang::get('rainlab.builder::lang.form.property_trigger_field_description'),
                        'type' => 'dropdown',
                        'fillFrom' => 'form-controls'
                    ],
                    [
                        'property' => 'condition',
                        'title' => Lang::get('rainlab.builder::lang.form.property_trigger_condition'),
                        'description' => Lang::get('rainlab.builder::lang.form.property_trigger_condition_description'),
                        'type' => 'autocomplete',
                        'items' => [
                            'checked' => Lang::get('rainlab.builder::lang.form.property_trigger_condition_checked'),
                            'unchecked' => Lang::get('rainlab.builder::lang.form.property_trigger_condition_unchecked'),
                            'value[somevalue]' => Lang::get('rainlab.builder::lang.form.property_trigger_condition_somevalue'),
                        ]
                    ]
                ]
            ],
            'preset' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_preset_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_preset_description'),
                'type' => 'object',
                'group' => Lang::get('rainlab.builder::lang.form.property_group_advanced'),
                'ignoreIfPropertyEmpty' => 'field',
                'properties' => [
                    [
                        'property' => 'field',
                        'title' => Lang::get('rainlab.builder::lang.form.property_preset_field'),
                        'description' => Lang::get('rainlab.builder::lang.form.property_preset_field_description'),
                        'type' => 'dropdown',
                        'fillFrom' => 'form-controls'
                    ],
                    [
                        'property' => 'type',
                        'title' => Lang::get('rainlab.builder::lang.form.property_preset_type'),
                        'description' => Lang::get('rainlab.builder::lang.form.property_preset_type_description'),
                        'type' => 'dropdown',
                        'options' => [
                            'url' => 'URL',
                            'file' => 'File',
                            'slug' => 'Slug',
                            'camel' => 'Camel'
                        ]
                    ]
                ]
            ],
            'attributes' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_attributes_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_attributes_description'),
                'type' => 'dictionary',
                'group' => Lang::get('rainlab.builder::lang.form.property_group_advanced'),
            ],
            'containerAttributes' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_container_attributes_title'),
                'description' => Lang::get('rainlab.builder::lang.form.property_container_attributes_description'),
                'type' => 'dictionary',
                'group' => Lang::get('rainlab.builder::lang.form.property_group_advanced'),
            ]
        ];

        $result = array_merge($result, $advancedProperties);

        foreach ($excludeProperties as $property) {
            if (array_key_exists($property, $result)) {
                unset($result[$property]);
            }
        }

        return $result;
    }

    protected function resolveControlGroupName($group)
    {
        if ($group == self::GROUP_STANDARD) {
            return Lang::get('rainlab.builder::lang.form.control_group_standard');
        }

        if ($group == self::GROUP_WIDGETS) {
            return Lang::get('rainlab.builder::lang.form.control_group_widgets');
        }

        return Lang::get($group);
    }
}
