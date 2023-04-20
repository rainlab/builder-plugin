<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use RainLab\Builder\Classes\TailorBlueprintLibrary;

/**
 * ContainerUtils
 */
trait ContainerUtils
{
    /**
     * @var object sourceModel
     */
    protected $sourceModel;

    /**
     * @var array relatedBlueprints
     */
    protected $relatedBlueprints;

    /**
     * setSourceModel
     */
    public function setSourceModel($sourceModel)
    {
        $this->sourceModel = $sourceModel;
    }

    /**
     * getBlueprintDefinition
     */
    public function getBlueprintDefinition()
    {
        return $this->sourceModel->getBlueprintObject();
    }

    /**
     * findRelatedModelClass
     */
    protected function findRelatedModelClass($relationName)
    {
        if ($this->relatedBlueprints === null) {
            $this->relatedBlueprints = TailorBlueprintLibrary::instance()->getRelatedBlueprintUuids($this->getBlueprintDefinition()->uuid);
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
            $this->relatedBlueprints = TailorBlueprintLibrary::instance()->getRelatedBlueprintUuids($this->getBlueprintDefinition()->uuid);
        }

        return $this->relatedBlueprints[$relationName] ?? null;
    }
}
