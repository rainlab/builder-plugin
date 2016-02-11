<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;
use ValidationException;
use Lang;

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

        $this->validateDupicateColumns();

        if (!$this->columns) {
            throw new ValidationException(['columns' => 'Please create at least one column.']);
        }
    }

    public function initDefaults()
    {
        $this->fileName = 'columns.yaml';
    }

    protected function validateDupicateColumns()
    {
        foreach ($this->columns as $outerIndex=>$outerColumn) {
            foreach ($this->columns as $innerIndex=>$innerColumn) {
                if ($innerIndex != $outerIndex && $innerColumn['field'] == $outerColumn['field']) {
                    throw new ValidationException([
                        'columns' => Lang::get('rainlab.builder::lang.list.error_duplicate_column', 
                            ['column' => $outerColumn['field']]
                        )
                    ]);
                }
            }
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

            $column = $this->preprocessColumnDataBeforeSave($column);

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
            if (!is_array($column)) {
                // Handle the case when a column is defined as
                // column: Title
                $column = [
                    'label' => $column 
                ];
            }

            $column['id'] = $index;
            $column['field'] = $columnName;

            $columns[] = $column;

            $index++;
        }

        $this->columns = $columns;
    }

    protected function preprocessColumnDataBeforeSave($column)
    {
        $booleanFields = [
            'searchable',
            'invisible',
            'sortable'
        ];

        $column = array_filter($column, function($value) 
        {
            return strlen($value) > 0;
        });

        foreach ($booleanFields as $booleanField) {
            if (!array_key_exists($booleanField, $column)) {
                continue;
            }

            $value = $column[$booleanField];
            if ($value == '1' || $value == 'true') {
                $value = true;
            }
            else {
                $value = false;
            }


            $column[$booleanField] = $value;
        }

        return $column;
    }
}