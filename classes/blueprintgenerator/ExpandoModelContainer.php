<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

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

        if (!$this->repeaterFieldset) {
            throw new ApplicationException('Missing repeater fieldset');
        }

        // Process join table entries specifically
        $fieldset = $this->repeaterFieldset;
        foreach ($fieldset->getAllFields() as $name => $field) {
            if ($field->type === 'entries') {
                $this->processEntryRelationDefinitions($definitions, $name, $field);
            }
            if ($field->type === 'repeater' || $field->type === 'nestedform') {
                $this->processRepeaterRelationDefinitions($definitions, $name, $field);
            }
        }

        return $definitions;
    }

    /**
     * processRepeaterRelationDefinitions
     */
    protected function processRepeaterRelationDefinitions(&$definitions, $fieldName, $fieldObj)
    {
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                if ($name === $fieldName && isset($props[0]) && $props[0] === \Tailor\Models\RepeaterItem::class) {
                    // Field to be jsonable (nested)
                    $this->addJsonable($name);
                    unset($relations[$name]);
                    break;
                }
            }
        }
    }
}
