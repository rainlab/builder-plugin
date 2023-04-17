<?php namespace {{namespace_php}}\Models;

use Model;

/**
 * {{studly_name}} Model
 */
class {{studly_name}} extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    /**
     * @var string table name
     */
    public $table = '{{namespace_table}}_{{snake_plural_name}}';

    /**
     * @var array rules for validation
     */
    public $rules = [];

    /**
     * @var array dates used by the model
     */
    protected $dates = [
        'deleted_at'
    ];

    /**
     * @var bool timestamps disabled by default
     */
    public $timestamps = false;
}
