<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

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
        $controls = new self;

        $controls->addFormField('title', 'Title')->span('auto');
        $controls->addFormField('slug', 'Slug')->preset(['field' => 'title', 'type' => 'slug'])->span('auto');

        return $controls->getControls();
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
            if ($relatedModelClass = $this->findRelatedModelClass($fieldName)) {
                $baseClass = mb_strtolower(class_basename($relatedModelClass));
                $path = $this->sourceModel->getPluginCodeObj()->toPluginDirectoryPath().'/models/'.$baseClass.'/columns.yaml';
                $fieldObj->list($path);
            }
        }

        // Remove tailor values
        $ignoreConfig = [
            'fieldName',
            'source',
            'column',
            'scope',
            'inverse',
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
