<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Backend\Classes\ListColumn;
use October\Contracts\Element\ListElement;
use October\Rain\Element\Lists\ColumnDefinition;

/**
 * ListElementContainer
 */
class ListElementContainer implements ListElement
{
    /**
     * @var array columns is a collection of columns
     */
    protected $columns = [];

    /**
     * defineColumn adds a column to the list element
     */
    public function defineColumn(string $columnName = null, string $label = null): ColumnDefinition
    {
        $column = (new ColumnDefinition)->label($label)->displayAs('text');

        $this->columns[$columnName] = $column;

        return $column;
    }

    /**
     * getControls
     */
    public function getControls(): array
    {
        $result = [];
        $index = 0;

        foreach ($this->columns as $name => $field) {
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
            'columnName',
            'source',
            'externalToolbarAppState',
            'externalToolbarEventBus'
        ];

        $parsedConfig = array_except((array) $config, $ignoreConfig);

        $parsedConfig['id'] = $index;
        $parsedConfig['field'] = $name;

        // Remove default values
        $defaultField = new ListColumn;
        foreach ($parsedConfig as $key => $value) {
            if ($key !== 'type' && $defaultField->$key === $value) {
                unset($parsedConfig[$key]);
            }
        }

        return $parsedConfig;
    }
}
