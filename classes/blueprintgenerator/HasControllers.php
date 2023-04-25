<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use RainLab\Builder\Models\ControllerModel;

/**
 * HasControllers
 */
trait HasControllers
{
    /**
     * validateController
     */
    protected function validateController()
    {
        $files = [];

        if ($model = $this->makeControllerModel()) {
            $files[] = $model->getControllerFilePath();
        }

        $this->validateUniqueFiles($files);

        $model && $model->validate();
    }

    /**
     * generateController
     */
    protected function generateController()
    {
        if ($controller = $this->makeControllerModel()) {
            $controller->save();
        }
    }

    /**
     * makeControllerModel
     */
    protected function makeControllerModel()
    {
        $controller = new ControllerModel;

        $controller->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $controller->baseModelClassName = $this->getConfig('modelClass');

        $controller->controllerName = $this->getConfig('name');

        $controller->controller = $this->getConfig('controllerClass');

        $controller->menuItem = $this->getActiveMenuItemCode();

        $controller->permissions = [$this->getConfig('permissionCode')];

        $controller->behaviors = [];

        if ($this->sourceModel->useListController()) {
            $controller->behaviors[] = \Backend\Behaviors\ListController::class;
        }

        $controller->behaviors[] = \Backend\Behaviors\FormController::class;

        return $controller;
    }
}
