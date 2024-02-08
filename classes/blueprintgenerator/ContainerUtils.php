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
     * findUuidFromSource
     */
    protected function findUuidFromSource($uuidOrHandle): ?string
    {
        if (!$this->sourceModel) {
            return null;
        }

        $blueprint = TailorBlueprintLibrary::instance()->getBlueprintObject($uuidOrHandle, $uuidOrHandle);
        if (!$blueprint) {
            return null;
        }

        return $blueprint->uuid;
    }

    /**
     * findRelatedModelClass
     */
    protected function findRelatedModelClass($uuidOrHandle): ?string
    {
        $uuid = $this->findUuidFromSource($uuidOrHandle);
        if (!$uuid) {
            return null;
        }

        $modelClass = $this->sourceModel->blueprints[$uuid]['modelClass'] ?? null;
        if (!$modelClass) {
            return null;
        }

        $pluginCodeObj = $this->sourceModel->getPluginCodeObj();
        return $pluginCodeObj->toPluginNamespace().'\\Models\\'.$modelClass;
    }
}
