<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Model;
use RainLab\Builder\Classes\TailorBlueprintLibrary;

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
     * @var array relatedBlueprints
     */
    protected $relatedBlueprints;

    /**
     * @var object sourceModel
     */
    protected $sourceModel;

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

        foreach ($definitions as $type => &$relations) {
            foreach ($relations as $name => &$props) {
                $props = $this->processRelationDefinition($type, $name, $props);
            }
        }

        return $definitions;
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
}
