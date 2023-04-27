<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Tailor\Classes\Blueprint\GlobalBlueprint;
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

        if ($model = $this->makeModelModel()) {
            $files[] = $model->getModelFilePath();
        }

        if ($form = $this->makeModelFormFields()) {
            $files[] = $form->getYamlFilePath();
        }

        if ($lists = $this->makeModelListColumns()) {
            $files[] = $lists->getYamlFilePath();
        }

        if ($filter = $this->makeModelFilterScopes()) {
            $files[] = $filter->getYamlFilePath();
        }

        $this->validateUniqueFiles($files);

        $model && $model->validate();
        $form && $form->validate();
        $lists && $lists->validate();
        $filter && $filter->validate();
    }

    /**
     * generateModel
     */
    protected function generateModel()
    {
        if ($filter = $this->makeModelFilterScopes()) {
            $filter->save();
            $this->filesGenerated[] = $filter->getYamlFilePath();
        }

        if ($lists = $this->makeModelListColumns()) {
            $lists->save();
            $this->filesGenerated[] = $lists->getYamlFilePath();
        }

        if ($form = $this->makeModelFormFields()) {
            $form->save();
            $this->filesGenerated[] = $form->getYamlFilePath();
        }

        if ($model = $this->makeModelModel()) {
            $model->save();
            $this->filesGenerated[] = $model->getModelFilePath();
        }
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

        // Custom logic for settings models
        if ($this->sourceModel->useSettingModel()) {
            $model->baseClassName = \System\Models\SettingModel::class;

            $model->addSoftDeleting = false;
        }

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

        $model->relationDefinitions = (array) $container->getProcessedRelationDefinitions();

        $model->validationDefinitions = (array) $container->getValidationDefinitions();

        $model->validationDefinitions['rules'] += ['title' => 'required'];

        if ($this->sourceModel->useMultisite()) {
            $model->traits[] = \October\Rain\Database\Traits\Multisite::class;

            $model->multisiteDefinition = (array) $container->getMultisiteDefinition();
        }

        if ($this->sourceModel->useStructure()) {
            $model->traits[] = \October\Rain\Database\Traits\NestedTree::class;
        }
    }

    /**
     * makeModelFormFields
     */
    protected function makeModelFormFields()
    {
        $model = new ModelFormModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'fields.yaml';

        $container = new FormElementContainer;

        $container->setSourceModel($this->sourceModel);

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllFormFields($container, ['context' => '*']);

        $model->controls = [
            'fields' => $container->getPrimaryControls(),
            'tabs' => ['fields' => $container->getControls()]
        ];

        return $model;
    }

    /**
     * makeModelListColumns
     */
    protected function makeModelListColumns()
    {
        $model = new ModelListModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'columns.yaml';

        $container = new ListElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllListColumns($container);

        $container->postProcessControls();

        $model->columns = $container->getPrimaryControls() + $container->getControls();

        if (!$model->columns) {
            return null;
        }

        return $model;
    }

    /**
     * makeModelFilterScopes
     */
    protected function makeModelFilterScopes()
    {
        $model = new ModelFilterModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($this->getConfig('modelClass'));

        $model->fileName = 'scopes.yaml';

        $container = new FilterElementContainer;

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        $fieldset->defineAllFilterScopes($container);

        $model->scopes = $container->getControls();

        if (!$model->scopes) {
            return null;
        }

        return $model;
    }
}
