<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Str;
use Model;
use ApplicationException;

/**
 * ModelContainer
 */
class ModelContainer extends Model
{
    use \RainLab\Builder\Classes\BlueprintGenerator\ContainerUtils;

    /**
     * @var array rules for validation
     *
     */
    public $rules = [];

    /**
     * @var array attributeNames of custom attributes
     *
     */
    public $attributeNames = [];

    /**
     * @var array customMessages of custom error messages
     *
     */
    public $customMessages = [];

    /**
     * @var array propagatable list of attributes to propagate to other sites.
     */
    protected $propagatable = [];

    /**
     * addPropagatable attributes for the model.
     * @param  array|string|null  $attributes
     */
    public function addPropagatable($attributes = null)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $this->propagatable = array_merge($this->propagatable, $attributes);
    }

    /**
     * getBlueprintAttribute
     */
    public function getBlueprintAttribute()
    {
        return $this->getBlueprintDefinition();
    }

    /**
     * getProcessedRelationDefinitions
     */
    public function getProcessedRelationDefinitions()
    {
        $definitions = parent::getRelationDefinitions();

        // Process specific field types
        $fieldset = $this->sourceModel->getBlueprintFieldset();
        foreach ($fieldset->getAllFields() as $name => $field) {
            if ($field->type === 'entries') {
                $this->processEntryRelationDefinitions($definitions, $name, $field);
            }
            if ($field->type === 'repeater') {
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
                    $repeaterInfo = $this->getRepeaterTableInfoFor($fieldName, $fieldObj);
                    $pluginCodeObj = $this->sourceModel->getPluginCodeObj();
                    $props[0] = $pluginCodeObj->toPluginNamespace().'\\Models\\'.$repeaterInfo['modelClass'];
                    $props['key'] = 'parent_id';
                    unset($props['relationClass']);
                    break;
                }
            }
        }
    }

    /**
     * getRepeaterTableInfoFor
     */
    public function getRepeaterTableInfoFor($fieldName, $fieldObj): ?array
    {
        $tableName = $this->sourceModel->getBlueprintConfig('tableName');
        if (!$tableName) {
            throw new ApplicationException('Missing a table name for repeaters');
        }

        $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
        $repeaterTable = $tableName .= '_' . mb_strtolower($fieldName) . '_items';
        $repeaterModelClass = $modelClass . Str::studly($fieldName) . 'Item';

        return [
            'fieldName' => $fieldName,
            'tableName' => $repeaterTable,
            'modelClass' => $repeaterModelClass
        ];
    }

    /**
     * processEntryRelationDefinitions
     */
    protected function processEntryRelationDefinitions(&$definitions, $fieldName, $fieldObj)
    {
        $foundDefinition = null;
        $foundAsType = null;
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                if ($name === $fieldName) {
                    // (╯°□°)╯︵ ┻━┻
                    $foundDefinition = array_pull($relations, $name);
                    $foundAsType = $type;
                    break;
                }
            }
        }

        if (!$foundDefinition) {
            return;
        }

        // Clean up and replace class
        if ($overrideClass = $this->findRelatedModelClass($fieldObj->source)) {
            $foundDefinition[0] = $overrideClass;
        }
        elseif ($overrideBlueprint = $this->findUuidFromSource($fieldObj->source)) {
            $foundDefinition['blueprint'] = $overrideBlueprint;
        }

        unset($foundDefinition['relationClass']);

        // This converts custom tailor relations to standard belongs to many
        if (isset($foundDefinition['table'])) {
            $joinInfo = $fieldObj->inverse
                ? $this->getInverseJoinTableInfoFor($fieldName, $fieldObj, $foundDefinition)
                : $this->getJoinTableInfoFor($fieldName, $fieldObj);

            if ($joinInfo) {
                $foundAsType = 'belongsToMany';
                $foundDefinition['table'] = $joinInfo['tableName'];
                unset($foundDefinition['name']);

                // Generic key for blueprints
                if ($joinInfo['isBlueprint']) {
                    $foundDefinition['otherKey'] = $joinInfo['relatedKey'];
                }

                // Swap keys
                if ($fieldObj->inverse) {
                    $foundDefinition['key'] = $joinInfo['relatedKey'];
                    $foundDefinition['otherKey'] = $joinInfo['parentKey'];
                }
            }
        }

        // ┬─┬ノ( º _ ºノ)
        $definitions[$foundAsType][$fieldName] = $foundDefinition;
    }

    /**
     * getJoinTableInfoFor
     */
    public function getJoinTableInfoFor($fieldName, $fieldObj): ?array
    {
        $tableName = $this->sourceModel->getBlueprintConfig('tableName');
        if (!$tableName) {
            throw new ApplicationException('Missing a table name for joins');
        }

        $joinTable = $tableName .= '_' . mb_strtolower($fieldName) . '_join';

        $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
        $relatedModelClass = $this->findRelatedModelClass($fieldObj->source);
        if (!$modelClass) {
            return null;
        }

        $parentKey = Str::snake(class_basename($modelClass)).'_id';
        $relatedKey = $relatedModelClass
            ? Str::snake(class_basename($relatedModelClass)).'_id'
            : 'relation_id';

        return [
            'fieldName' => $fieldName,
            'tableName' => $joinTable,
            'parentKey' => $parentKey,
            'relatedKey' => $relatedKey,
            'isBlueprint' => !$relatedModelClass
        ];
    }

    /**
     * getInverseJoinTableInfoFor
     */
    public function getInverseJoinTableInfoFor($fieldName, $fieldObj, $foundDefinition): ?array
    {
        $relatedUuid = $this->findUuidFromSource($fieldObj->source);
        if (!$relatedUuid) {
            return null;
        }

        // Determine join table
        $tableName = $this->sourceModel->blueprints[$relatedUuid]['tableName'] ?? null;
        if ($tableName) {
            $joinTable = $tableName .= '_' . mb_strtolower($fieldObj->inverse) . '_join';
        }
        else {
            $joinTable = $foundDefinition['table'] ?? 'unknown';
        }

        $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
        $relatedModelClass = $this->findRelatedModelClass($fieldObj->source);
        if (!$modelClass) {
            return null;
        }

        $parentKey = Str::snake(class_basename($modelClass)).'_id';
        $relatedKey = $relatedModelClass
            ? Str::snake(class_basename($relatedModelClass)).'_id'
            : 'relation_id';

        return [
            'tableName' => $joinTable,
            'parentKey' => $parentKey,
            'relatedKey' => $relatedKey,
            'isBlueprint' => !$relatedModelClass
        ];
    }

    /**
     * getValidationDefinitions
     */
    public function getValidationDefinitions()
    {
        return [
            'rules' => $this->rules,
            'attributeNames' => $this->attributeNames,
            'customMessages' => $this->customMessages,
        ];
    }

    /**
     * getMultisiteDefinition
     */
    public function getMultisiteDefinition()
    {
        $multisiteSync = $this->blueprint instanceof \Tailor\Classes\Blueprint\EntryBlueprint &&
            $this->blueprint->useMultisiteSync();

        return [
            'fields' => $this->propagatable,
            'sync' => $multisiteSync
        ];
    }
}
