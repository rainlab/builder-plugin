<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use Lang;
use Yaml;
use File;
use Twig;
use Tailor\Classes\SchemaBuilder;
use Tailor\Classes\BlueprintIndexer;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use RainLab\Builder\Classes\ControllerGenerator;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;

/**
 * HasControllers
 */
trait HasControllers
{
    /**
     * validateController
     */
    protected function validateController()
    {

    }

    /**
     * generateController
     */
    protected function generateController()
    {
        $controllerClass = $this->getConfig('controllerClass');
        if (!$controllerClass) {
            throw new ApplicationException('Missing a controller class name');
        }

        $generator = new ControllerGenerator($this->sourceModel);
        $generator->generate();
    }
}
