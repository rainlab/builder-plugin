<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Str;
use Model;
use ApplicationException;

/**
 * ExpandoModelContainer
 */
class ExpandoModelContainer extends ModelContainer
{
    /**
     * @var object repeaterFieldset
     */
    public $repeaterFieldset;

    /**
     * getProcessedRelationDefinitions
     */
    public function getProcessedRelationDefinitions()
    {
        $definitions = parent::getRelationDefinitions();

        // Clean up props
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                $props = $this->processRelationDefinition($type, $name, $props);
            }
        }

        if (!$this->repeaterFieldset) {
            throw new ApplicationException('Missing repeater fieldset');
        }

        // Process join table entries specifically
        $fieldset = $this->repeaterFieldset;
        foreach ($fieldset->getAllFields() as $name => $field) {
            if ($field->type === 'entries') {
                $this->processEntryRelationDefinitions($definitions, $name, $field);
            }
        }

        return $definitions;
    }

    /**
     * getRepeaterTableInfoFor
     */
    public function getRepeaterTableInfoFor($fieldName, $fieldObj): ?array
    {
        $tableName = $this->sourceModel->getBlueprintConfig('tableName');
        if (!$tableName) {
            throw new ApplicationException('Missing a table name');
        }

        $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
        $repeaterTable = $tableName .= '_' . mb_strtolower($fieldName) . '_items';
        $repeaterModelClass = $modelClass . Str::studly($fieldName) . 'Item';

        return [
            'tableName' => $repeaterTable,
            'modelClass' => $repeaterModelClass
        ];
    }
}
