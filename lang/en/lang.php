<?php

return [
    'plugin' => [
        'name' => 'Builder',
        'description' => 'Provides visual tools for building October plugins.',
        'add' => 'Create plugin',
        'no_records' => 'No plugins found',
        'no_description' => 'No description',
        'no_name' => 'No name',
        'search' => 'Search...',
        'filter_description' => 'Display all plugins or only your plugins.',
    ],
    'author_name' => [
        'title' => 'Author Name',
        'description' => 'Default author name to use for your new plugins. The author name is not fixed - you can change it in the plugins configuration at any time.'
    ],
    'namespace' => [
        'title' => 'Namespace',
        'description' => 'If you develop for the Marketplace, the namespace should match the author code and cannot be changed. Refer to the documentation for details.'
    ],
    'database' => [
        'menu_label' => 'Database'
    ],
    'model' => [
        'menu_label' => 'Models'
    ],
    'controller' => [
        'menu_label' => 'Controllers'
    ],
    'version' => [
        'menu_label' => 'Versions'
    ],
    'menu' => [
        'menu_label' => 'Backend Menu'
    ],
    'localization' => [
        'menu_label' => 'Localization'
    ],
    'permission' => [
        'menu_label' => 'Permissions'
    ]
];