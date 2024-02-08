<?php namespace RainLab\Builder\Classes\StandardControlsRegistry;

use Lang;
use RainLab\Builder\Classes\ControlLibrary;

/**
 * HasFormUi
 */
trait HasFormUi
{
    /**
     * registerSectionControl
     */
    protected function registerSectionControl()
    {
        $excludeProperties = [
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
            ControlLibrary::GROUP_UI,
            'icon-minus',
            $this->controlLibrary->getStandardProperties($excludeProperties),
            null
        );
    }

    /**
     * registerHintControl
     */
    protected function registerHintControl()
    {
        $excludeProperties = [
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

        $properties = [
            'path' =>  [
                'title' => Lang::get('rainlab.builder::lang.form.property_hint_path'),
                'description' => Lang::get('rainlab.builder::lang.form.property_hint_path_description'),
                'type' => 'string',
                'sortOrder' => 81
            ],
            'mode' => [
                'title' => Lang::get('rainlab.builder::lang.form.property_display_mode'),
                'description' => Lang::get('rainlab.builder::lang.form.property_display_mode_description'),
                'type' => 'dropdown',
                'default' => 'info',
                'options' => [
                    'tip' => Lang::get('rainlab.builder::lang.form.class_mode_tip'),
                    'info' => Lang::get('rainlab.builder::lang.form.class_mode_info'),
                    'warning' => Lang::get('rainlab.builder::lang.form.class_mode_warning'),
                    'danger' => Lang::get('rainlab.builder::lang.form.class_mode_danger'),
                    'success' => Lang::get('rainlab.builder::lang.form.class_mode_success'),
                ],
                'sortOrder' => 82
            ]
        ];

        $this->controlLibrary->registerControl(
            'hint',
            'rainlab.builder::lang.form.control_hint',
            'rainlab.builder::lang.form.control_hint_description',
            ControlLibrary::GROUP_UI,
            'icon-question-circle',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }

    /**
     * registerRulerControl
     */
    protected function registerRulerControl()
    {
        $excludeProperties = [
            'label',
            'stretch',
            'default',
            'placeholder',
            'required',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'oc.comment',
            'oc.commentPosition',
            'disabled'
        ];

        $this->controlLibrary->registerControl(
            'ruler',
            'rainlab.builder::lang.form.control_ruler',
            'rainlab.builder::lang.form.control_ruler_description',
            ControlLibrary::GROUP_UI,
            'icon-minus',
            $this->controlLibrary->getStandardProperties($excludeProperties),
            null
        );
    }

    /**
     * registerPartialControl
     */
    protected function registerPartialControl()
    {
        $excludeProperties = [
            'stretch',
            'default',
            'placeholder',
            'required',
            'defaultFrom',
            'dependsOn',
            'preset',
            'attributes',
            'oc.commentPosition',
            'oc.comment',
            'disabled'
        ];

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
            ControlLibrary::GROUP_UI,
            'icon-file-text-o',
            $this->controlLibrary->getStandardProperties($excludeProperties, $properties),
            null
        );
    }
}
