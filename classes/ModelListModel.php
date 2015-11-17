<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use ValidationException;
use Exception;
use Lang;
use File;

/**
 * Represents and manages model lists.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelListModel extends ModelYamlModel
{
    public $columns;

    protected static $fillable = [
        'fileName',
        'columns'
    ];

    protected $validationRules = [
        'fileName' => ['required', 'regex:/^[a-z0-9\.\-]+$/i']
    ];

    public function loadForm($path)
    {
        $this->fileName = $path;
        
        return parent::load($this->getFilePath());
    }

    public function fill(array $attributes)
    {
        if (!is_array($attributes['columns'])) {
            $attributes['columns'] = json_decode($attributes['columns'], true);

            if ($attributes['columns'] === null) {
                throw new SystemException('Cannot decode columns JSON string.');
            }
        }

        return parent::fill($attributes);
    }

    public static function validateFileIsModelType($fileContentsArray)
    {
        $modelRootNodes = [
            'columns'
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

        if (!$this->columns) {
            throw new ValidationException(['columns' => 'Please create at least one column.']);
        }
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        $fileColumns = [];

        foreach ($this->columns as $column) {
            if (!isset($column['field'])) {
                throw new ApplicationException('Cannot save the list - the column field name should not be empty.');
            }

            $columnName = $column['field'];
            unset($column['field']);

            if (array_key_exists('id', $column)) {
                unset($column['id']);
            }

            $fileColumns[$columnName]  = $column;
        }

        return [
            'columns'=>$fileColumns
        ];
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $fileColumns = $array['columns'];
        $columns = [];
        $index = 0;

        foreach ($fileColumns as $columnName=>$column) {
            $column['id'] = $index;
            $column['field'] = $columnName;

            $columns[] = $column;

            $index++;
        }

        $this->columns = $columns;
    }
}