<?php namespace RainLab\Builder\Models;

use October\Rain\Database\Model;

/**
 * Settings for builder
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array implement behaviors
     */
    public $implement = [\System\Behaviors\SettingsModel::class];

    /**
     * @var string settingsCode is a unique code for this object.
     */
    public $settingsCode = 'rainlab_builder_settings';

    /**
     * @var mixed settingsFields defitions
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var array rules to be applied to the data.
     */
    public $rules = [
        'author_name' => 'required',
        'author_namespace' => ['required', 'regex:/^[a-z]+[a-z0-9]+$/i', 'reserved']
    ];
}
