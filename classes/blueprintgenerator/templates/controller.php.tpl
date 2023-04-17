<?php namespace {{ pluginNamespace }}\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * {{controller}} Model
 */
class {{ controller }} extends Controller
{
    /**
     * @var array implement extensions
     */
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    /**
     * @var array listConfig is `ListController` configuration.
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var string formConfig is `FormController` configuration.
     */
    public $formConfig = 'config_form.yaml';
}
