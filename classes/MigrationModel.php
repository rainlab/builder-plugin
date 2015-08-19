<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use ApplicationException;
use SystemException;
use Validator;
use Lang;

/**
 * Manages plugin migrations
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class MigrationModel extends BaseModel
{
    protected static $fillable = [
        'version',
        'description',
        'code'
    ];

    protected $validationRules = [
        'version' => ['required', 'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/'],
        'description' => ['required'],
        'code' => ['required'],
    ];

    public function validate()
    {
        $this->validationMessages = [
            'version.regex' => Lang::get('rainlab.builder::lang.migration.error_version_invalid')
        ];

        return parent::validate();
    }
}