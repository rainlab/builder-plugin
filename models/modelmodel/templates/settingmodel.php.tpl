<?php namespace {namespace};

use System\Models\SettingModel;

/**
 * Model
 */
class {classname} extends SettingModel
{
{traitContents}
{dynamicContents}

    /**
     * @var string settingsCode used by the model.
     */
    public $settingsCode = '{table}';

    /**
     * @var string settingsFields yaml definition.
     */
    public $settingsFields = 'fields.yaml';
{validationContents}{multisiteContents}{relationContents}
}
