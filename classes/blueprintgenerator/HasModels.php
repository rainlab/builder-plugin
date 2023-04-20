<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Models\ModelFormModel;
use RainLab\Builder\Models\ModelListModel;
use RainLab\Builder\Models\ModelFilterModel;
use RainLab\Builder\Classes\BlueprintGenerator\ModelContainer;
use RainLab\Builder\Classes\BlueprintGenerator\ListElementContainer;
use RainLab\Builder\Classes\BlueprintGenerator\FormElementContainer;
use RainLab\Builder\Classes\BlueprintGenerator\FilterElementContainer;

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

        $filter = $this->makeModelFilterModel();
        $files[] = $filter->getYamlFilePath();

        $this->validateUniqueFiles($files);

        $model->validate();
        $form->validate();
        $lists->validate();
        $filter->validate();
    }

    /**
     * generateModel
     */
    protected function generateModel()
    {
        $filter = $this->makeModelFilterModel();
        $filter->save();

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
        $model = new ModelModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->className = $this->getConfig('modelClass');

        $model->databaseTable = $this->getConfig('tableName');

        $model->addTimestamps = true;

        $model->addSoftDeleting = true;

        $model->skipDbValidation = true;

        $model->traits[] = \Tailor\Traits\BlueprintRelationModel::class;

        $this->extendModelWithModelSpecs($model);

        return $model;
    }

    /**
     * extendModelWithModelSpecs
     */
    protected function extendModelWithModelSpecs($model)
    {
        $container = new ModelContainer;

        $container->setSourceModel($this->sourceModel);

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->applyModelExtensions($container);

        $model->relationDefinitions = (array) $container->getRelationDefinitions();

        $model->validationDefinitions = (array) $container->getValidationDefinitions();

        if ($container->useMultisite()) {
            $model->traits[] = \October\Rain\Database\Traits\Multisite::class;

            $model->multisiteDefinition = (array) $container->getMultisiteDefinition();
        }
    }

    /**
     * makeModelFormModel
     */
    protected function makeModelFormModel()
    {
        $model = new ModelFormModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'fields.yaml';

        $container = new FormElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllFormFields($container);

        $model->controls = [
            'fields' => $container->getPrimaryControls(),
            'tabs' => ['fields' => $container->getControls()]
        ];

        return $model;
    }

    /**
     * makeModelListModel
     */
    protected function makeModelListModel()
    {
        $model = new ModelListModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'columns.yaml';

        $container = new ListElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllListColumns($container);

        $container->postProcessControls();

        $model->columns = $container->getControls();

        return $model;
    }

    /**
     * makeModelFilterModel
     */
    protected function makeModelFilterModel()
    {
        $model = new ModelFilterModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'scopes.yaml';

        $container = new FilterElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllFilterScopes($container);

        $model->scopes = $container->getControls();

        return $model;
    }
}
