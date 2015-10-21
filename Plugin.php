<?php namespace RainLab\Builder;

use Backend;
use System\Classes\PluginBase;
use Event;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'rainlab.builder::lang.plugin.name',
            'description' => 'rainlab.builder::lang.plugin.description',
            'author'      => 'Alexey Bobkov, Samuel Georges',
            'icon'        => 'icon-wrench',
            'homepage'    => 'https://github.com/rainlab/builder-plugin'
        ];
    }

    public function registerComponents()
    {
    }

    public function registerPermissions()
    {
    }

    public function registerNavigation()
    {
        return [
            'builder' => [
                'label'       => 'rainlab.builder::lang.plugin.name',
                'url'         => Backend::url('rainlab/builder'),
                'icon'        => 'icon-wrench',
                'permissions' => ['rainlab.builder.*'],
                'order'       => 40, 

                'sideMenu' => [
                    'database' => [
                        'label'       => 'rainlab.builder::lang.database.menu_label',
                        'icon'        => 'icon-hdd-o',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'database'],
                        'permissions' => ['rainlab.builder.*']
                    ],
                    'models' => [
                        'label'       => 'rainlab.builder::lang.model.menu_label',
                        'icon'        => 'icon-random',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'models'],
                        'permissions' => ['rainlab.builder.*']
                    ],
                    'controllers' => [
                        'label'       => 'rainlab.builder::lang.controller.menu_label',
                        'icon'        => 'icon-asterisk',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'controller'],
                        'permissions' => ['rainlab.builder.*']
                    ],
                    'menus' => [
                        'label'       => 'rainlab.builder::lang.menu.menu_label',
                        'icon'        => 'icon-location-arrow',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'menu'],
                        'permissions' => ['rainlab.builder.*']
                    ],
                    'versions' => [
                        'label'       => 'rainlab.builder::lang.version.menu_label',
                        'icon'        => 'icon-code-fork',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'version'],
                        'permissions' => ['rainlab.builder.*']
                    ],
                    'localization' => [
                        'label'       => 'rainlab.builder::lang.localization.menu_label',
                        'icon'        => 'icon-globe',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'localization'],
                        'permissions' => ['rainlab.builder.*']
                    ],
                    'permissions' => [
                        'label'       => 'rainlab.builder::lang.permission.menu_label',
                        'icon'        => 'icon-unlock-alt',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'permission'],
                        'permissions' => ['rainlab.builder.*']
                    ]
                ]

            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'config' => [
                'label'       => 'Builder',
                'icon'        => 'icon-wrench',
                'description' => 'Set your plugins namespace and author name.',
                'class'       => 'RainLab\Builder\Models\Settings',
                'order'       => 60
            ]
        ];
    }

    public function boot()
    {
        Event::listen('pages.builder.registerControls', function($controlLibrary) {
            $controlLibrary->registerControl('text', 
                'rainlab.builder::lang.form.control_text',
                null,
                $controlLibrary::GROUP_STANDARD,
                'icon-terminal',
                $controlLibrary->getStandardProperties(),
                null
            );

            $controlLibrary->registerControl('password', 
                'rainlab.builder::lang.form.control_password',
                null,
                $controlLibrary::GROUP_STANDARD,
                'icon-lock',
                $controlLibrary->getStandardProperties(),
                null
            );

            $controlLibrary->registerControl('checkbox', 
                'rainlab.builder::lang.form.control_checkbox',
                null,
                $controlLibrary::GROUP_STANDARD,
                'icon-check-square-o',
                $controlLibrary->getStandardProperties(['commentAbove']),
                null
            );

            $controlLibrary->registerControl('switch', 
                'rainlab.builder::lang.form.control_switch',
                null,
                $controlLibrary::GROUP_STANDARD,
                'icon-toggle-on',
                $controlLibrary->getStandardProperties(['commentAbove']),
                null
            );
        });
    }
}
