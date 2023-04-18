<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use Lang;
use Yaml;
use File;
use Twig;
use Tailor\Classes\SchemaBuilder;
use Tailor\Classes\BlueprintIndexer;
use RainLab\Builder\Models\MenusModel;
use RainLab\Builder\Models\PermissionsModel;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;

/**
 * HasNavigation
 */
trait HasNavigation
{
    /**
     * generateNavigation
     */
    protected function generateNavigation()
    {
        $blueprint = $this->sourceModel->getBlueprintObject();

        $indexer = BlueprintIndexer::instance();

        $indexer->findPrimaryNavigation($blueprint->uuid);

        $indexer->findSecondaryNavigation($blueprint->uuid);
    }

    /**
     * loadOrCreateMenusModel
     */
    protected function loadOrCreateMenusModel()
    {
        $model = new MenusModel;

        $model->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        return $model;
    }
}
