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
        $controls = [];

        $controls['title'] = (new FieldDefinition)->label('Title')->span('full')->getConfig();
        $controls['slug'] = (new FieldDefinition)->label('Slug')->preset(['field' => 'title', 'type' => 'slug'])->getConfig();
        $controls['is_enabled'] = (new FieldDefinition)->label('Enabled')->displayAs('switch')->defaults(true)->getConfig();
        $controls['published_at'] = (new FieldDefinition)->label('Publish Date')->displayAs('datepicker')->defaultTimeMidnight()->getConfig();
        $controls['expired_at'] = (new FieldDefinition)->label('Expiry Date')->displayAs('datepicker')->defaultTimeMidnight()->getConfig();
        $controls['parent_id'] = (new FieldDefinition)->label('Parent')->displayAs('dropdown')->getConfig();

        return $controls;
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

        if (isset($config['span']) && $config['span'] === 'adaptive') {
            $config['span'] = 'full';
        }

        return $parsedConfig;
    }
}
