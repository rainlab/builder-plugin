<?php namespace RainLab\Builder\Behaviors;

use Backend\Classes\ControllerBehavior;
use RainLab\Builder\Classes\PluginCode;
use RainLab\Builder\Classes\LocalizationModel;
use RainLab\Builder\Classes\ModelModel;
use RainLab\Builder\Classes\ModelFormModel;
use RainLab\Builder\Classes\ModelListModel;
use RainLab\Builder\Classes\ControllerModel;
use RainLab\Builder\Classes\PermissionsModel;
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
        $code = Input::get('registry_plugin_code');
        $type = Input::get('registry_data_type');
        $subtype = Input::get('registry_data_subtype');

        $result = null;

        switch ($type) {
            case 'localization':
                $result = LocalizationModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-classes':
                $result = ModelModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-forms':
                $result = ModelFormModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-lists':
                $result = ModelListModel::getPluginRegistryData($code, $subtype);
                break;
            case 'controller-urls':
                $result = ControllerModel::getPluginRegistryData($code, $subtype);
                break;
            case 'model-columns':
                $result = ModelModel::getPluginRegistryDataColumns($code, $subtype);
                break;
            case 'plugin-lists':
                $result = ModelListModel::getPluginRegistryDataAllRecords($code);
                break;
            case 'permissions':
                $result = PermissionsModel::getPluginRegistryData($code);
                break;
            default:
                throw new SystemException('Unknown plugin registry data type requested.');
        }

        return ['registryData' => $result];
    }
}
