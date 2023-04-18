<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Lang;
use File;
use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Models\ModelFormModel;
use RainLab\Builder\Models\ModelListModel;
use RainLab\Builder\Classes\BlueprintGenerator\ListElementContainer;
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

        $lists = $this->makeModelListModel();
        $files[] = $lists->getYamlFilePath();

        $this->validateUniqueFiles($files);

        $model->validate();
        $form->validate();
        $lists->validate();
    }

    /**
     * generateModel
     */
    protected function generateModel()
    {
        $lists = $this->makeModelListModel();
        $lists->save();

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
     * makeModelFormModel
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

        $model->controls = [
            'tabs' => ['fields' => $container->getControls()]
        ];

        return $model;
    }

    /**
     * makeModelListModel
     */
    protected function makeModelListModel()
    {
        $model = new ModelListModel();

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'columns.yaml';

        $container = new ListElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllListColumns($container);

        $model->columns = $container->getControls();

        return $model;
    }
}
