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
        'settings' => 'Settings',
        'entity_name' => 'Plugin',
        'field_name' => 'Name',
        'field_author' => 'Author',
        'field_description' => 'Description',
        'field_icon' => 'Plugin icon',
        'field_plugin_namespace' => 'Plugin namespace',
        'field_author_namespace' => 'Author namespace',
        'field_namespace_description' => 'Namespace can contain only Latin letters and digits and should start with a Latin letter.',
        'field_author_namespace_description' => 'You cannot change the namespaces with Builder after you create the plugin.',
    ],
    'author_name' => [
        'title' => 'Author name',
        'description' => 'Default author name to use for your new plugins. The author name is not fixed - you can change it in the plugins configuration at any time.'
    ],
    'author_namespace' => [
        'title' => 'Author namespace',
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
    ],
    'yaml' => [
        'save_error' => "Error saving file ':name'. Please check write permissions."
    ],
    'common' => [
        'error_file_exists' => "File already exists: ':path'.",
        'field_icon_description' => 'October uses Font Autumn icons: http://daftspunk.github.io/Font-Autumn/',
        'destination_dir_not_exists' => "The destination directory doesn't exist: ':path'.",
        'error_make_dir' => "Error creating directory: ':name'.",
        'error_dir_exists' => "Directory already exists: ':path'.",
        'template_not_found' => "Template file is not found: ':name'.",
        'error_generating_file' => "Error generating file: ':path'.",
        'error_loading_template' => "Error loading template file: ':name'."
    ]
];