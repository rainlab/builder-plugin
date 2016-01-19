<?php namespace RainLab\Builder\Behaviors;

use Backend\Classes\ControllerBehavior;
use RainLab\Builder\Classes\PluginCode;
use RainLab\Builder\Classes\LocalizationModel;
use ApplicationException;
use SystemException;
use Exception;
use Request;
use Flash;
use Input;
use Lang;

/**
 * Plugin data registry functionality for the Builder index controller
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class IndexDataRegistry extends ControllerBehavior
{
    public function onPluginDataRegistryGetData()
    {
        $code = Input::get('plugin_code');
        $type = Input::get('type');
        $subtype = Input::get('subtype');

        $result = null;

        switch ($type) {
            case 'localization': 
                $result = LocalizationModel::getPluginRegistryData($code, $subtype);
                break;
            default: 
                throw new SystemException('Unknown plugin registry data type requested.');
        }

        return ['registryData' => $result];
    }
}