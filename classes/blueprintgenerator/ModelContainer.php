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

        // Clean up props and replace model classes
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                $this->processRelationDefinition($type, $name, $props);
            }
        }

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

    /**
     * processEntryRelationDefinitions
     */
    protected function processEntryRelationDefinitions(&$definitions, $fieldName, $fieldObj)
    {
        if ($fieldObj->maxItems === 1) {
            return;
        }

        $foundDefinition = null;
        $foundAsType = null;
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                if ($name === $fieldName && isset($props['table'])) {
                    // (╯°□°)╯︵ ┻━┻
                    $foundDefinition = array_pull($relations, $name);
                    $foundAsType = $type;
                    break;
                }
            }
        }

        // This converts custom tailor relations to standard belongs to many
        if ($foundDefinition) {
            $joinInfo = $fieldObj->inverse
                ? $this->getInverseJoinTableInfoFor($fieldName, $fieldObj)
                : $this->getJoinTableInfoFor($fieldName, $fieldObj);

            if ($joinInfo) {
                unset($foundDefinition['name']);
                $foundDefinition['table'] = $joinInfo['tableName'];

                // Swap keys
                if ($fieldObj->inverse) {
                    $foundDefinition['key'] = $joinInfo['relatedKey'];
                    $foundDefinition['otherKey'] = $joinInfo['parentKey'];
                }

                $definitions['belongsToMany'][$fieldName] = $foundDefinition;
            }
            else {
                // ┬─┬ノ( º _ ºノ)
                $definitions[$foundAsType][$fieldName] = $foundDefinition;
            }
        }
    }

    /**
     * getJoinTableInfoFor
     */
    public function getJoinTableInfoFor($fieldName, $fieldObj): ?array
    {
        $tableName = $this->sourceModel->getBlueprintConfig('tableName');
        if (!$tableName) {
            throw new ApplicationException('Missing a table name');
        }

        $joinTable = $tableName .= '_' . mb_strtolower($fieldName) . '_join';

        $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
        $relatedModelClass = $this->findRelatedModelClass($fieldName);
        if (!$relatedModelClass || !$modelClass) {
            return null;
        }

        $parentKey = Str::snake(class_basename($modelClass)).'_id';
        $relatedKey = Str::snake(class_basename($relatedModelClass)).'_id';

        return [
            'tableName' => $joinTable,
            'parentKey' => $relatedKey,
            'relatedKey' => $parentKey,
        ];
    }

    /**
     * getInverseJoinTableInfoFor
     */
    public function getInverseJoinTableInfoFor($fieldName, $fieldObj): ?array
    {
        $relatedUuid = $this->findRelatedBlueprintUuid($fieldName);
        if (!$relatedUuid) {
            return null;
        }

        $tableName = $this->sourceModel->blueprints[$relatedUuid]['tableName'] ?? null;
        if (!$tableName) {
            throw new ApplicationException('Missing a table name');
        }

        $joinTable = $tableName .= '_' . mb_strtolower($fieldObj->inverse) . '_join';

        $modelClass = $this->sourceModel->getBlueprintConfig('modelClass');
        $relatedModelClass = $this->findRelatedModelClass($fieldName);
        if (!$relatedModelClass || !$modelClass) {
            return null;
        }

        $parentKey = Str::snake(class_basename($modelClass)).'_id';
        $relatedKey = Str::snake(class_basename($relatedModelClass)).'_id';

        return [
            'tableName' => $joinTable,
            'parentKey' => $relatedKey,
            'relatedKey' => $parentKey,
        ];
    }

    /**
     * processRelationDefinition
     */
    protected function processRelationDefinition($type, $name, &$props)
    {
        // Ignore file attachments
        if (starts_with($type, 'attach')) {
            return;
        }

        if ($overrideClass = $this->findRelatedModelClass($name)) {
            $props[0] = $overrideClass;
        }
        elseif ($overrideBlueprint = $this->findRelatedBlueprintUuid($name)) {
            $props['blueprint'] = $overrideBlueprint;
        }

        unset($props['relationClass']);
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
     * useMultisite
     */
    public function useMultisite()
    {
        return $this->blueprint->useMultisite();
    }

    /**
     * getMultisiteDefinition
     */
    public function getMultisiteDefinition()
    {
        return [
            'fields' => $this->propagatable,
            'sync' => $this->blueprint->useMultisiteSync()
        ];
    }
}
