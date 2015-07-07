<?php namespace RainLab\Builder\Models;

use October\Rain\Database\Model;

/**
 * Builder settings model
 *
 * @package system
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'rainlab_builder_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation rules
     */
    public $rules = [
        'author_name' => 'required',
        'namespace' => 'required'
    ];
}