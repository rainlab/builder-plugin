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
    /**
     * addFormField adds a field to the fieldset
     */
    public function addFormField(string $fieldName = null, string $label = null): FieldDefinition
    {
        $field = (new FieldDefinition)->label($label);

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
     * getControls
     */
    public function getControls(): array
    {
        $result = [];

        foreach ($this->getAllFields() as $name => $field) {
            $result[$name] = $this->parseFieldConfig($field->config);
        }

        return $result;
    }

    /**
     * parseFieldConfig
     */
    protected function parseFieldConfig($config): array
    {
        // Remove tailor values
        $ignoreConfig = [
            'fieldName',
            'source',
            'externalToolbarAppState',
            'externalToolbarEventBus'
        ];

        $parsedConfig = array_except((array) $config, $ignoreConfig);

        // Remove default values
        $defaultField = new FormField;
        foreach ($parsedConfig as $key => $value) {
            if ($defaultField->$key === $value) {
                unset($parsedConfig[$key]);
            }
        }

        return $parsedConfig;
    }
}
