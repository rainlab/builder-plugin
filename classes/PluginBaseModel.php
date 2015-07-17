<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use ApplicationException;
use SystemException;
use Exception;
use Lang;
use File;

/**
 * Manages plugin basic information.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginBaseModel extends YamlModel
{
    public $name;

    public $namespace;

    public $description;

    public $author;

    public $icon;

    public $author_namespace;

    public $homepage;

    protected $yamlSection = "plugin";

    protected static $fillable = [
        'name',
        'author',
        'namespace',
        'author_namespace',
        'description',
        'icon',
        'homepage'
    ];

    protected $validationRules = [
        'name' => 'required',
        'author'   => ['required'],
        'namespace'   => ['required', 'regex:/^[a-z]+[a-z0-9]+$/i'],
        'author_namespace' => ['required', 'regex:/^[a-z]+[a-z0-9]+$/i'],
        'homepage' => 'url'
    ];

    public function getIconOptions($keyValue = null)
    {
        return IconList::getList();
    }

    public function initDefaults()
    {
        $settings =  PluginSettings::instance();
        $this->author = $settings->author_name;
        $this->author_namespace = $settings->author_namespace;
    }

    public function getPluginCode()
    {
        return $this->author_namespace.'.'.$this->namespace;
    }

    public function loadPlugin($pluginCode)
    {
        $pluginCodeObj = new PluginCode($pluginCode);

        $filePath = self::pluginSettingsFileExists($pluginCodeObj);
        if ($filePath === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.plugin.error_settings_not_editable'));
        }

        $this->author_namespace = $pluginCodeObj->getAuthorCode();
        $this->namespace = $pluginCodeObj->getPluginCode();

        return parent::load($filePath);
    }

    public static function pluginSettingsFileExists($pluginCodeObj)
    {
        $filePath = File::symbolizePath($pluginCodeObj->toPluginFilePath());
        if (File::isFile($filePath)) {
            return $filePath;
        }

        return false;
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    protected function modelToYamlArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'author' => $this->author,
            'icon' => $this->icon,
            'homepage' => $this->homepage
        ];
    }

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    protected function yamlArrayToModel($array)
    {
        $this->name = $this->getArrayKeySafe($array, 'name');
        $this->description = $this->getArrayKeySafe($array, 'description');
        $this->author = $this->getArrayKeySafe($array, 'author');
        $this->icon = $this->getArrayKeySafe($array, 'icon');
        $this->homepage = $this->getArrayKeySafe($array, 'homepage');
    }

    protected function afterCreate()
    {
        try {
            $this->initPluginStructure();
            $this->forcePluginRegistration();
        }
        catch (Exception $ex) {
            $this->rollbackPluginCreation();
            throw $ex;
        }
    }

    protected function initPluginStructure()
    {
        $basePath = $this->getPluginPath();

        $structure = [
            $basePath.'/Plugin.php' => 'plugin.php.tpl',
            $basePath.'/updates/version.yaml' => 'version.yaml.tpl',
            $basePath.'/classes'
        ];

        $variables = [
            'authorNamespace' => $this->author_namespace,
            'pluginNamespace' => $this->namespace
        ];

        $generator = new FilesystemGenerator('$', $structure, '$/rainlab/builder/classes/pluginbasemodel/templates');
        $generator->setVariables($variables);
        $generator->generate();
    }

    protected function forcePluginRegistration()
    {
        PluginManager::instance()->loadPlugins();
        UpdateManager::instance()->update();
    }

    protected function rollbackPluginCreation()
    {
        $basePath = '$/'.$this->getPluginPath();
        $basePath = File::symbolizePath($basePath);

        if (basename($basePath) == strtolower($this->namespace)) {
            File::deleteDirectory($basePath);
        }
    }

    /**
     * Returns a file path to save the model to.
     * @return string Returns a path.
     */
    protected function getFilePath()
    {
        return $this->getPluginPathObj()->toPluginFilePath();
    }

    protected function getPluginPath()
    {
        return $this->getPluginPathObj()->toFilesystemPath();
    }

    protected function getPluginPathObj()
    {
        return new PluginCode($this->getPluginCode());
    }
}