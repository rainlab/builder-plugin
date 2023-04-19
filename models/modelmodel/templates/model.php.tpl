<?php namespace {namespace};

use Model;

/**
 * Model
 */
class {classname} extends Model
{
{traitContents}
{dynamicContents}

    /**
     * @var string table in the database used by the model.
     */
    public $table = '{table}';

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];
{relationContents}
}
