<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Str;
use Backend\Classes\FormField;
use October\Contracts\Element\FormElement;
use October\Rain\Element\Form\FieldDefinition;
use October\Rain\Element\Form\FieldsetDefinition;

/**
 * FormElementContainer
 */
class FormElementContainer extends FieldsetDefinition implements FormElement
{
    use \RainLab\Builder\Classes\BlueprintGenerator\ContainerUtils;

    /**
     * addFormField adds a field to the fieldset
     */
    public function addFormField(string $fieldName = null, string $label = null): FieldDefinition
    {
        $field = (new FieldDefinition)->label($label)->displayAs('text');

        $this->addField($fieldName, $field);

        return $field;
    }

    /**
     * getFormFieldset returns the current fieldset definition
     */
    public function getFormFieldset(): FieldsetDefinition
    {
        return $this;
    }

    /**
     * getFormContext returns the current form context, e.g. create, update
     */
    public function getFormContext()
    {
        return '';
    }

    /**
     * getPrimaryControls
     */
    public function getPrimaryControls()
    {
        $host = new self;

        $host->addFormField('title', 'Title')->span('auto');
        $host->addFormField('slug', 'Slug')->preset(['field' => 'title', 'type' => 'slug'])->span('auto');

        return $host->getControls();
    }

    /**
     * getControls
     */
    public function getControls(): array
    {
        $result = [];

        foreach ($this->getAllFields() as $name => $field) {
            $result[$name] = $this->parseFieldConfig($name, $field);
        }

        return $result;
    }

    /**
     * parseFieldConfig
     */
    protected function parseFieldConfig($fieldName, $fieldObj): array
    {
        // Apply mutations to field object
        if ($fieldObj->span === 'adaptive') {
            $fieldObj->span('full');
        }

        if ($fieldObj->type === 'recordfinder') {
            $relatedModelClass = $this->findRelatedModelClass($fieldObj->source);
            if ($relatedModelClass) {
                $baseClass = mb_strtolower(class_basename($relatedModelClass));
                $path = $this->sourceModel->getPluginCodeObj()->toPluginDirectoryPath().'/models/'.$baseClass;
                $fieldObj->list($path.'/columns.yaml');
            }
        }

        if ($fieldObj->type === 'repeater') {
            $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
            $baseClass = mb_strtolower(class_basename($modelClass)).mb_strtolower(Str::studly($fieldName)).'item';
            $path = $this->sourceModel->getPluginCodeObj()->toPluginDirectoryPath().'/models/'.$baseClass;
            if ($fieldObj->groups) {
                $newGroups = [];
                foreach ($fieldObj->groups as $groupName => $groupConfig) {
                    $newGroups[$groupName] = $path."/fields_{$groupName}.yaml";
                }
                $fieldObj->groups($newGroups);
            }
            else {
                $fieldObj->form($path.'/fields.yaml');
            }
        }

        // Remove tailor values
        $ignoreConfig = [
            'fieldName',
            'source',
            'column',
            'scope',
            'inverse',
            'validation',
            'externalToolbarAppState',
            'externalToolbarEventBus'
        ];

        $parsedConfig = array_except((array) $fieldObj->config, $ignoreConfig);

        // Remove default values
        $keepDefaults = [
            'type',
            'span',
        ];

        $defaultField = new FormField;
        foreach ($parsedConfig as $key => $value) {
            if (!in_array($key, $keepDefaults) && $defaultField->$key === $value) {
                unset($parsedConfig[$key]);
            }
        }

        return $parsedConfig;
    }
}
