<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Str;
use Model;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use ApplicationException;

/**
 * ModelContainer
 */
class ModelContainer extends Model
{
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
     * @var array relatedBlueprints
     */
    protected $relatedBlueprints;

    /**
     * @var object sourceModel
     */
    protected $sourceModel;

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
     * getBlueprintDefinition
     */
    public function getBlueprintDefinition()
    {
        return $this->sourceModel->getBlueprintObject();
    }

    /**
     * setSourceModel
     */
    public function setSourceModel($sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    /**
     * getRelationDefinitions
     */
    public function getRelationDefinitions()
    {
        $definitions = parent::getRelationDefinitions();

        // Clean up props
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                $props = $this->processRelationDefinition($type, $name, $props);
            }
        }

        // Process join table entries specifically
        $fieldset = $this->sourceModel->getBlueprintFieldset();
        foreach ($fieldset->getAllFields() as $name => $field) {
            if ($field->type === 'entries' && $field->maxItems !== 1) {
                $this->processEntryRelationDefinitions($definitions, $name, $field);
            }
        }

        return $definitions;
    }

    /**
     * processEntryRelationDefinitions
     */
    protected function processEntryRelationDefinitions(&$definitions, $fieldName, $fieldObj)
    {
        $foundDefinition = null;
        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                if ($name === $fieldName) {
                    $foundDefinition = array_pull($relations, $name);
                }
            }
        }

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
            }

            $definitions['belongsToMany'][$fieldName] = $foundDefinition;
        }
    }

    /**
     * getJoinTableFor
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
     * getJoinTableFor
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
    protected function processRelationDefinition($type, $name, $props)
    {
        // Ignore file attachments
        if (starts_with($type, 'attach')) {
            return $props;
        }

        if ($overrideClass = $this->findRelatedModelClass($name)) {
            $props[0] = $overrideClass;
        }
        elseif ($overrideBlueprint = $this->findRelatedBlueprintUuid($name)) {
            $props['blueprint'] = $overrideBlueprint;
        }

        unset($props['relationClass']);

        return $props;
    }

    /**
     * findRelatedModelClass
     */
    protected function findRelatedModelClass($relationName)
    {
        if ($this->relatedBlueprints === null) {
            $this->relatedBlueprints = TailorBlueprintLibrary::instance()->getRelatedBlueprintUuids($this->blueprint->uuid);
        }

        if (isset($this->relatedBlueprints[$relationName])) {
            $uuid = $this->relatedBlueprints[$relationName];
            $modelClass = $this->sourceModel->blueprints[$uuid]['modelClass'] ?? null;
            if ($modelClass) {
                $pluginCodeObj = $this->sourceModel->getPluginCodeObj();
                return $pluginCodeObj->toPluginNamespace().'\\Models\\'.$modelClass;
            }
        }
    }

    /**
     * findRelatedBlueprintUuid
     */
    protected function findRelatedBlueprintUuid($relationName)
    {
        if ($this->relatedBlueprints === null) {
            $this->relatedBlueprints = TailorBlueprintLibrary::instance()->getRelatedBlueprintUuids($this->blueprint->uuid);
        }

        return $this->relatedBlueprints[$relationName] ?? null;
    }

    /**
     * getValidationDefinitions
     */
    public function getValidationDefinitions()
    {
        return [
            'rules' => $this->rules + ['title' => 'required'],
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
