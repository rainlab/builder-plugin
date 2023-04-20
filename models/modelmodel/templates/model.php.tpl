<?php namespace {namespace};

use {baseclass};

/**
 * Model
 */
class {classname} extends {baseclassname}
{
{traitContents}
{dynamicContents}

    /**
     * @var string table in the database used by the model.
     */
    public $table = '{table}';
{validationContents}{multisiteContents}{relationContents}
}
