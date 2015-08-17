<?php namespace RainLab\Builder\Classes;

use ValidationException;
use Validator;

/**
 * Base class for Builder models.
 *
 * Builder models manage various types of records - database metadata objects,
 * YAML files, etc.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class BaseModel
{
    /**
     * @var boolean This property is used by the system internally.
     */
    public $exists = false;

    protected $validationRules = [];

    protected $validationMessages = [];

    protected static $fillable = [];

    protected $updatedData = [];

    public function fill(array $attributes)
    {
        $this->updatedData = [];

        foreach ($attributes as $key => $value) {
            if (!in_array($key, static::$fillable)) {
                continue;
            }

            $methodName = 'set'.ucfirst($key);
            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
            else {
                $this->$key = $value;
            }

            $this->updatedData[$key] = $value;
        }
    }

    public function validate()
    {
        $existingData = [];
        foreach (static::$fillable as $field) {
            $existingData[$field] = $this->$field;
        }

        $validation = Validator::make(
            array_merge($existingData, $this->updatedData),
            $this->validationRules,
            $this->validationMessages
        );

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        if (!$this->isNewModel()) {
            $this->validateBeforeCreate();
        }
    }

    protected function validateBeforeCreate()
    {
    }

    protected function isNewModel()
    {
        return $this->exists === true;
    }
}