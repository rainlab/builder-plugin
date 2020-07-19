<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use ValidationException;
use Lang;

/**
 * Manages plugin permissions information.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PermissionsModel extends PluginYamlModel
{
    public $permissions = [];

    protected $yamlSection = 'permissions';

    protected $pluginCodeObj;

    protected static $fillable = [
        'permissions'
    ];

    public function setPluginCodeObj($pluginCodeObj)
    {
        $this->pluginCodeObj = $pluginCodeObj;
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        $filePermissions = [];

        foreach ($this->permissions as $permission) {
            if (array_key_exists('id', $permission)) {
                unset($permission['id']);
            }

            $permission = $this->trimPermissionProperties($permission);

            if ($this->isEmptyRow($permission)) {
                continue;
            }

            if (!isset($permission['permission'])) {
                throw new ApplicationException('Cannot save permissions - the permission code should not be empty.');
            }

            $code = $permission['permission'];
            unset($permission['permission']);

            $filePermissions[$code]  = $permission;
        }

        return $filePermissions;
    }

    public function validate()
    {
        parent::validate();

        $this->validateDupicatePermissions();
        $this->validateRequiredProperties();
    }

    public static function getPluginRegistryData($pluginCode)
    {
        $model = new PermissionsModel();

        $model->loadPlugin($pluginCode);

        $result = [];

        foreach ($model->permissions as $permissionInfo) {
            if (!isset($permissionInfo['permission']) || !isset($permissionInfo['label'])) {
                continue;
            }

            $key = $permissionInfo['permission'];
            $result[$key] = $key.' - '.Lang::get($permissionInfo['label']);
        }

        return $result;
    }

    protected function validateDupicatePermissions()
    {
        foreach ($this->permissions as $outerIndex => $outerPermission) {
            if (!isset($outerPermission['permission'])) {
                continue;
            }

            foreach ($this->permissions as $innerIndex => $innerPermission) {
                if (!isset($innerPermission['permission'])) {
                    continue;
                }

                $outerCode = trim($outerPermission['permission']);
                $innerCode = trim($innerPermission['permission']);

                if ($innerIndex != $outerIndex && $outerCode == $innerCode && strlen($outerCode)) {
                    throw new ValidationException([
                        'permissions' => Lang::get(
                            'rainlab.builder::lang.permission.error_duplicate_code',
                            ['code' => $outerCode]
                        )
                    ]);
                }
            }
        }
    }

    protected function validateRequiredProperties()
    {
        foreach ($this->permissions as $permission) {
            if (array_key_exists('id', $permission)) {
                unset($permission['id']);
            }

            $permission = $this->trimPermissionProperties($permission);

            if ($this->isEmptyRow($permission)) {
                continue;
            }

            if (!strlen($permission['permission'])) {
                throw new ValidationException([
                    'permissions' => Lang::get('rainlab.builder::lang.permission.column_permission_required')
                ]);
            }

            if (!strlen($permission['label'])) {
                throw new ValidationException([
                    'permissions' => Lang::get('rainlab.builder::lang.permission.column_label_required')
                ]);
            }

            if (!strlen($permission['tab'])) {
                throw new ValidationException([
                    'permissions' => Lang::get('rainlab.builder::lang.permission.column_tab_required')
                ]);
            }
        }
    }

    protected function trimPermissionProperties($permission)
    {
        array_walk($permission, function ($value, $key) {
            return trim($value);
        });

        return $permission;
    }

    protected function isEmptyRow($permission)
    {
        return !isset($permission['tab']) || !isset($permission['permission']) || !isset($permission['label']);
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $filePermissions = $array;
        $permissions = [];
        $index = 0;

        foreach ($filePermissions as $code => $permission) {
            $permission['permission'] = $code;

            $permissions[] = $permission;
        }

        $this->permissions = $permissions;
    }

    /**
     * Returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        if ($this->pluginCodeObj === null) {
            throw new SystemException('Error saving plugin permission model - the plugin code object is not set.');
        }

        return $this->pluginCodeObj->toPluginFilePath();
    }
}
