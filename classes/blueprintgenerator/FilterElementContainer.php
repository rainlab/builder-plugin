<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Backend\Classes\FilterScope;
use October\Contracts\Element\FilterElement;
use October\Rain\Element\Filter\ScopeDefinition;

/**
 * FilterElementContainer
 */
class FilterElementContainer implements FilterElement
{
    /**
     * @var array scopes is a collection of scopes
     */
    protected $scopes = [];

    /**
     * defineScope adds a scope to the list element
     */
    public function defineScope(string $scopeName = null, string $label = null): ScopeDefinition
    {
        $scope = (new ScopeDefinition)->label($label)->displayAs('text');

        $this->scopes[$scopeName] = $scope;

        return $scope;
    }

    /**
     * getControls
     */
    public function getControls(): array
    {
        $result = [];
        $index = 0;

        foreach ($this->scopes as $name => $field) {
            $result[$name] = $this->parseFieldConfig($index, $name, $field->config);
            $index++;
        }

        return $result;
    }

    /**
     * parseFieldConfig
     */
    protected function parseFieldConfig($index, $name, $config): array
    {
        // Remove tailor values
        $ignoreConfig = [
            'scopeName',
            'source',
            'externalToolbarAppState',
            'externalToolbarEventBus'
        ];

        $parsedConfig = array_except((array) $config, $ignoreConfig);

        $parsedConfig['id'] = $index;
        $parsedConfig['field'] = $name;

        // Remove default values
        $keepDefaults = [
            'type',
        ];

        $defaultField = new FilterScope;
        foreach ($parsedConfig as $key => $value) {
            if (!in_array($key, $keepDefaults) && $defaultField->$key === $value) {
                unset($parsedConfig[$key]);
            }
        }

        return $parsedConfig;
    }
}
