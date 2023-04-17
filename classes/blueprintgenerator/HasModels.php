<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use Lang;
use Yaml;
use File;
use Twig;
use Tailor\Classes\SchemaBuilder;
use Tailor\Classes\BlueprintIndexer;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;

/**
 * HasModels
 */
trait HasModels
{
    /**
     * validateModel
     */
    protected function validateModel()
    {

    }

    /**
     * generateModel
     */
    protected function generateModel()
    {
        if (!isset($this->activeConfig['modelClass'])) {
            throw new ApplicationException('Missing a model class name');
        }

        $modelClass = $this->activeConfig['modelClass'];
    }
}
