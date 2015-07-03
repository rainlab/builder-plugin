<?php namespace RainLab\Builder;

use Backend;
use System\Classes\PluginBase;

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
                'order'       => 40
            ]
        ];
    }
}
