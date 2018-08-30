<?php namespace {namespace};

use Model;

/**
 * Model
 */
class {classname} extends Model
{
    use \October\Rain\Database\Traits\Validation;
    {dynamicContents}
    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = '{table}';
}
