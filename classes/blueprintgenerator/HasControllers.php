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

        $model = $this->makeControllerModel();
        $files[] = $model->getControllerFilePath();

        $this->validateUniqueFiles($files);

        $model->validate();
    }

    /**
     * generateController
     */
    protected function generateController()
    {
        $controller = $this->makeControllerModel();
        $controller->save();
    }

    /**
     * makeControllerModel
     */
    protected function makeControllerModel()
    {
        $controller = new ControllerModel();

        $controller->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $controller->baseModelClassName = $this->getConfig('modelClass');

        $controller->controller = $this->getConfig('controllerClass');

        $controller->permissions = [$this->getConfig('permissionCode')];

        $controller->behaviors = [
            \Backend\Behaviors\ListController::class,
            \Backend\Behaviors\FormController::class,
        ];

        return $controller;
    }
}
