<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Tailor\Classes\FieldManager;
use RainLab\Builder\Models\ModelModel;
use RainLab\Builder\Models\ModelFormModel;
use RainLab\Builder\Classes\BlueprintGenerator\FormElementContainer;
use RainLab\Builder\Classes\BlueprintGenerator\ExpandoModelContainer;

/**
 * HasExpandoModels
 */
trait HasExpandoModels
{
    /**
     * validateExpandoModels
     */
    protected function validateExpandoModels()
    {
        foreach ($this->makeExpandoModels(true) as $model) {
            $this->validateUniqueFiles([$model->getModelFilePath()]);
            $model->validate();
        }
    }

    /**
     * generateExpandoModels
     */
    protected function generateExpandoModels()
    {
        foreach ($this->makeExpandoModels() as $model) {
            $model->save();
            $this->filesGenerated[] = $model->getModelFilePath();
        }
    }

    /**
     * makeExpandoModels
     */
    protected function makeExpandoModels($isValidate = false)
    {
        $models = [];

        $fieldset = $this->sourceModel->getBlueprintFieldset();

        foreach ($fieldset->getAllFields() as $name => $field) {
            if ($field->type !== 'repeater') {
                continue;
            }

            $container = new ExpandoModelContainer;

            $container->setSourceModel($this->sourceModel);

            $fieldset = $container->repeaterFieldset = $this->makeExpandoRepeaterFieldset($field);

            $fieldset->applyModelExtensions($container);

            // Generate form fields
            $this->generateExpandoModelFormFields($container, $name, $field, $isValidate);

            // Generate model
            $models[] = $this->makeExpandoModelModel($container, $name, $field);
        }

        return $models;
    }

    /**
     * generateExpandoModelModel
     */
    protected function makeExpandoModelModel($container, $name, $field)
    {
        $repeaterInfo = $container->getRepeaterTableInfoFor($name, $field);

        $model = new ModelModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->className = $repeaterInfo['modelClass'];

        $model->databaseTable = $repeaterInfo['tableName'];

        $model->addTimestamps = true;

        $model->skipDbValidation = true;

        $model->traits[] = \Tailor\Traits\BlueprintRelationModel::class;

        $model->baseClassName = \October\Rain\Database\ExpandoModel::class;

        $model->relationDefinitions = (array) $container->getProcessedRelationDefinitions();

        $model->validationDefinitions = (array) $container->getValidationDefinitions();

        $model->addRawContentToModel(<<<PHP

    /**
     * @var array expandoPassthru attributes that should not be serialized
     */
    protected \$expandoPassthru = ['parent_id'];
PHP);

        if ($jsonable = $container->getJsonable()) {
            $jsonableStr = '';
            foreach ($jsonable as $j) {
                $jsonableStr = "'".$j."', ";
            }
            $jsonableStr = trim($jsonableStr, ', ');
            $model->addRawContentToModel(<<<PHP


    /**
     * @var array jsonable attribute names that are json encoded and decoded from the database
     */
    protected \$jsonable = [$jsonableStr];
PHP);
        }

        return $model;
    }

    /**
     * makeExpandoRepeaterFieldset
     */
    protected function makeExpandoRepeaterFieldset($field)
    {
        if (!$field->groups) {
            return FieldManager::instance()->makeFieldset((array) $field->form);
        }

        // Create a merged fieldset for groups to acquire relations
        $fieldsets = [];
        foreach ($field->groups as $config) {
            $fieldsets[] = FieldManager::instance()->makeFieldset($config);
        }

        $fieldset = array_shift($fieldsets);
        foreach ($fieldsets as $otherFieldset) {
            foreach ($otherFieldset->getAllFields() as $name => $field) {
                $fieldset->addField($name, $field);
            }
        }

        return $fieldset;
    }

    /**
     * generateExpandoModelFormFields generates form fields YAML files
     */
    protected function generateExpandoModelFormFields($container, $name, $field, $isValidate = false)
    {
        $repeaterInfo = $container->getRepeaterTableInfoFor($name, $field);

        $forms = [];
        if ($field->groups) {
            foreach ($field->groups as $groupName => $groupConfig) {
                $forms[] = $this->makeExpandoModelFormFields($repeaterInfo, $groupConfig, $groupName);
            }
        }
        elseif ($field->form) {
            $forms[] = $this->makeExpandoModelFormFields($repeaterInfo, $field->form);
        }

        foreach ($forms as $form) {
            if ($isValidate) {
                $this->validateUniqueFiles([$form->getYamlFilePath()]);
                $form->validate();
            }
            else {
                $form->save();
                $this->filesGenerated[] = $form->getYamlFilePath();
            }
        }
    }

    /**
     * makeExpandoModelFormFields
     */
    protected function makeExpandoModelFormFields($repeaterInfo, $formConfig, $groupPrefix = '')
    {
        $model = new ModelFormModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        $model->setModelClassName($repeaterInfo['modelClass']);

        $model->fileName = $groupPrefix ? "fields_{$groupPrefix}.yaml" : 'fields.yaml';

        $container = new FormElementContainer;

        $container->setSourceModel($this->sourceModel);

        $fieldset = FieldManager::instance()->makeFieldset($formConfig);

        $fieldset->defineAllFormFields($container, ['context' => '*']);

        $model->controls = array_except($formConfig, 'fields') + [
            'fields' => $container->getControls(),
        ];

        return $model;
    }
}
