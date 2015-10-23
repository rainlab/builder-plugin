<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use ValidationException;
use Exception;
use Lang;
use File;

/**
 * Represents and manages model forms.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelFormModel extends ModelYamlModel
{
    public $controls;

    protected static $fillable = [
        'fileName',
        'controls'
    ];

    protected $validationRules = [
        'fileName' => ['required', 'regex:/^[a-z0-9\.\-]+$/i']
    ];

    public function loadForm($path)
    {
        $this->fileName = $path;
        
        return parent::load($this->getFilePath());
    }

    public static function validateFileIsModelType($fileContentsArray)
    {
        $modelRootNodes = [
            'fields',
            'tabs',
            'secondaryTabs'
        ];

        foreach ($modelRootNodes as $node) {
            if (array_key_exists($node, $fileContentsArray)) {
                return true;
            }
        }

        return false;
    }

    public function validate()
    {
        parent::validate();

        if (!$this->controls) {
            throw new ValidationException(['controls' => 'Please create at least one field.']);
        }
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        return $this->controls;
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->controls = $array;
    }
}