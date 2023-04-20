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
     * postProcessControls
     */
    public function postProcessControls()
    {
        foreach ($this->columns as $name => $field) {
            if ($field->type === 'partial' && starts_with($field->path, '~/modules/tailor/contentfields/')) {
                $field->displayAs('text')->path(null)->relation($name)->select('title');
            }
        }
    }

    /**
     * getPrimaryControls
     */
    public function getPrimaryControls()
    {
        $host = new self;

        $host->defineColumn('id', 'ID')->invisible();
        $host->defineColumn('title', 'Title')->searchable(true);
        $host->defineColumn('slug', 'Slug')->searchable(true)->invisible();

        return $host->getControls();
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
        $keepDefaults = [
            'type',
        ];

        $defaultField = new ListColumn;
        foreach ($parsedConfig as $key => $value) {
            if (!in_array($key, $keepDefaults) && $defaultField->$key === $value) {
                unset($parsedConfig[$key]);
            }
        }

        return $parsedConfig;
    }
}
