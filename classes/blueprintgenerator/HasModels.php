<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Lang;
use File;
use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Models\ModelFormModel;
use RainLab\Builder\Models\ModelListModel;
use RainLab\Builder\Classes\BlueprintGenerator\FormElementContainer;
use ApplicationException;
use ValidationException;

/**
 * HasModels
 */
trait HasModels
{
    /**
     * validateModel
     */
    protected function validateModel()
    {
        $files = [];

        $model = $this->makeModelModel();
        $files[] = $model->getModelFilePath();

        $form = $this->makeModelFormModel();
        $files[] = $form->getYamlFilePath();

        $this->validateUniqueFiles($files);

        $model->validate();
        $form->validate();
    }

    /**
     * generateModel
     */
    protected function generateModel()
    {
        $form = $this->makeModelFormModel();
        $form->save();

        $model = $this->makeModelModel();
        $model->save();
    }

    /**
     * makeModelModel
     */
    protected function makeModelModel()
    {
        $model = new ModelModel();

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->className = $this->getConfig('modelClass');

        $model->databaseTable = $this->getConfig('tableName');

        $model->addTimestamps = true;

        $model->addSoftDeleting = true;

        $model->skipDbValidation = true;

        return $model;
    }

    /**
     * makeModelModel
     */
    protected function makeModelFormModel()
    {
        $model = new ModelFormModel();

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'fields.yaml';

        $container = new FormElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllFormFields($container);

        $model->controls = ['fields' => $container->getControls()];

        return $model;
    }
}
