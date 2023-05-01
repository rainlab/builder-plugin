<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use RainLab\Builder\Models\PermissionsModel;

/**
 * HasPermissions
 */
trait HasPermissions
{
    /**
     * generatePermission
     */
    protected function validatePermission()
    {
        if ($permissionCode = $this->getConfig('permissionCode')) {
            $blueprint = $this->sourceModel->getBlueprintObject();
            $model = $this->loadOrCreatePermissionsModel();
            $model->permissions[] = $this->makePermissionItem($blueprint, $permissionCode);
            $model->validate();
        }
    }

    /**
     * generatePermission
     */
    protected function generatePermission()
    {
        if ($permissionCode = $this->getConfig('permissionCode')) {
            $blueprint = $this->sourceModel->getBlueprintObject();
            $model = $this->loadOrCreatePermissionsModel();
            $model->permissions[] = $this->makePermissionItem($blueprint, $permissionCode);
            $model->save();
        }
    }

    /**
     * makePermissionItem
     */
    protected function makePermissionItem($blueprint, $code)
    {
        return [
            'permission' => $code,
            'tab' => $this->sourceModel->getPluginName(),
            'label' => __("Manage :name Items", ['name' => $blueprint->name]),
        ];
    }

    /**
     * loadOrCreatePermissionsModel
     */
    protected function loadOrCreatePermissionsModel()
    {
        $model = new PermissionsModel;

        $model->loadPlugin($this->sourceModel->getPluginCodeObj()->toCode());

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        return $model;
    }
}
