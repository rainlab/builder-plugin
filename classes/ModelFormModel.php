<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use Exception;
use Lang;
use File;

/**
 * Represents and manages models forms.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelFormModel extends YamlModel
{
    protected static $fillable = [
    ];

    protected $validationRules = [
    ];

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {

    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {

    }

    /**
     * Returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        
    }
}