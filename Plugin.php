<?php namespace RainLab\Builder;

use Event;
use Backend;
use System\Classes\PluginBase;
use RainLab\Builder\Classes\StandardControlsRegistry;
use RainLab\Builder\Classes\StandardBehaviorsRegistry;

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
        return [
            'RainLab\Builder\Components\RecordList'       => 'builderList',
            'RainLab\Builder\Components\RecordDetails'    => 'builderDetails'
        ];
    }

    public function registerPermissions()
    {
        return [
            'rainlab.builder.manage_plugins' => [
                'tab' => 'rainlab.builder::lang.plugin.name', 
                'label' => 'rainlab.builder::lang.plugin.manage_plugins']
        ];
    }

    public function registerNavigation()
    {
        return [
            'builder' => [
                'label'       => 'rainlab.builder::lang.plugin.name',
                'url'         => Backend::url('rainlab/builder'),
                'icon'        => 'icon-wrench',
                'iconSvg'     => 'plugins/rainlab/builder/assets/images/builder-icon.svg',
                'permissions' => ['rainlab.builder.manage_plugins'],
                'order'       => 40,

                'sideMenu' => [
                    'database' => [
                        'label'       => 'rainlab.builder::lang.database.menu_label',
                        'icon'        => 'icon-hdd-o',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'database'],
                        'permissions' => ['rainlab.builder.manage_plugins']
                    ],
                    'models' => [
                        'label'       => 'rainlab.builder::lang.model.menu_label',
                        'icon'        => 'icon-random',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'models'],
                        'permissions' => ['rrainlab.builder.manage_plugins']
                    ],
                    'permissions' => [
                        'label'       => 'rainlab.builder::lang.permission.menu_label',
                        'icon'        => 'icon-unlock-alt',
                        'url'         => '#',
                        'attributes'  => ['data-no-side-panel'=>'true', 'data-builder-command'=>'permission:cmdOpenPermissions', 'data-menu-item'=>'permissions'],
                        'permissions' => ['rainlab.builder.manage_plugins']
                    ],
                    'menus' => [
                        'label'       => 'rainlab.builder::lang.menu.menu_label',
                        'icon'        => 'icon-location-arrow',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-no-side-panel'=>'true', 'data-builder-command'=>'menus:cmdOpenMenus', 'data-menu-item'=>'menus'],
                        'permissions' => ['rainlab.builder.manage_plugins']
                    ],
                    'controllers' => [
                        'label'       => 'rainlab.builder::lang.controller.menu_label',
                        'icon'        => 'icon-asterisk',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'controllers'],
                        'permissions' => ['rainlab.builder.manage_plugins']
                    ],
                    'versions' => [
                        'label'       => 'rainlab.builder::lang.version.menu_label',
                        'icon'        => 'icon-code-fork',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'version'],
                        'permissions' => ['rainlab.builder.manage_plugins']
                    ],
                    'localization' => [
                        'label'       => 'rainlab.builder::lang.localization.menu_label',
                        'icon'        => 'icon-globe',
                        'url'         => 'javascript:;',
                        'attributes'  => ['data-menu-item'=>'localization'],
                        'permissions' => ['rainlab.builder.manage_plugins']
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
                'permissions' => ['rainlab.builder.manage_plugins'],
                'order'       => 600
            ]
        ];
    }

    public function boot()
    {
        Event::listen('pages.builder.registerControls', function($controlLibrary) {
            new StandardControlsRegistry($controlLibrary);
        });

        Event::listen('pages.builder.registerControllerBehaviors', function($behaviorLibrary) {
            new StandardBehaviorsRegistry($behaviorLibrary);
        });

    }
}
