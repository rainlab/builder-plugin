<?php namespace RainLab\Builder\Classes;

use ValidationException;
use SystemException;
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

    /**
     * @var PluginCode The plugin code object the model is associated with.
     */
    protected $pluginCodeObj = null;

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
                if (is_scalar($value) && strpos($value, ' ') !== false) {
                    $value = trim($value);
                }

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

    public function isNewModel()
    {
        return $this->exists === false;
    }

    /**
     * Sets a string code of a plugin the model is associated with
     * @param string $code Specifies the plugin code
     */
    public function setPluginCode($code)
    {
        $this->pluginCodeObj = new PluginCode($code);
    }

    /**
     * Sets a code object of a plugin the model is associated with
     * @param PluginCode $obj Specifies the plugin code object
     */
    public function setPluginCodeObj($obj)
    {
        $this->pluginCodeObj = $obj;
    }

    protected function validateBeforeCreate()
    {
    }

    public function getModelPluginName()
    {
        $pluginCodeObj = $this->getPluginCodeObj();
        $pluginCode = $pluginCodeObj->toCode();

        $vector = PluginVector::createFromPluginCode($pluginCode);
        if ($vector) {
            return $vector->getPluginName();
        }

        return null;
    }

    public function getPluginCodeObj()
    {
        if (!$this->pluginCodeObj) {
            throw new SystemException(sprintf('The active plugin is not set in the %s object.', get_class($this)));
        }

        return $this->pluginCodeObj;
    }
}