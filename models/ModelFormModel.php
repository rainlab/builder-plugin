<?php namespace RainLab\Builder\Models;

use ValidationException;
use SystemException;

/**
 * ModelFormModel represents and manages model forms.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelFormModel extends ModelYamlModel
{
    /**
     * @var array controls
     */
    public $controls;

    /**
     * @var array originals (attributes)
     */
    public $originals;

    /**
     * @var array fillable
     */
    protected static $fillable = [
        'fileName',
        'controls'
    ];

    /**
     * @var array validationRules
     */
    protected $validationRules = [
        'fileName' => ['required', 'regex:/^[a-z0-9\.\-_]+$/i']
    ];

    /**
     * loadForm
     */
    public function loadForm($path)
    {
        $this->fileName = $path;

        return parent::load($this->getFilePath());
    }

    /**
     * fill
     */
    public function fill(array $attributes)
    {
        if (!is_array($attributes['controls'])) {
            $attributes['controls'] = json_decode($attributes['controls'], true);

            if ($attributes['controls'] === null) {
                throw new SystemException('Cannot decode controls JSON string.');
            }
        }

        return parent::fill($attributes);
    }

    /**
     * validateFileIsModelType
     */
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

    /**
     * validate
     */
    public function validate()
    {
        parent::validate();

        if (!$this->controls) {
            throw new ValidationException(['controls' => 'Please create at least one field.']);
        }
    }

    /**
     * initDefaults
     */
    public function initDefaults()
    {
        $this->fileName = 'fields.yaml';
    }

    /**
     * modelToYamlArray converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        return array_merge((array) $this->originals, $this->controls);
    }

    /**
     * yamlArrayToModel loads the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->originals = array_except($array, 'fields');

        $this->controls = $array;
    }
}
