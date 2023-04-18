<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use Lang;
use Yaml;
use File;
use Twig;
use Tailor\Classes\SchemaBuilder;
use Tailor\Classes\BlueprintIndexer;
use RainLab\Builder\Models\MenusModel;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;

/**
 * HasPluginFile
 */
trait HasPluginFile
{
    /**
     * generateNavigation
     */
    protected function generatePluginUpdate()
    {
        $blueprint = $this->sourceModel->getBlueprintObject();

        $indexer = BlueprintIndexer::instance();

        $indexer->findPrimaryNavigation($blueprint->uuid);

        $indexer->findSecondaryNavigation($blueprint->uuid);
    }


    /**
     * loadOrCreateMenusModel
     */
    protected function loadOrCreateMenusModel($pluginCode)
    {
        $model = new MenusModel;

        $model->loadPlugin($pluginCode);

        return $model;
    }
}
