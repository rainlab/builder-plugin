<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use System\Classes\UpdateManager;
use System\Classes\PluginManager;
use ApplicationException;
use SystemException;
use Exception;
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
        return '$/'.$this->getPluginPath().'/plugin.yaml';
    }

    protected function getPluginPath()
    {
        if (!$this->validateNamespacePath($this->author_namespace) || !$this->validateNamespacePath($this->namespace)) {
            throw new SystemException('Invalid plugin or author namespace');
        }

        return strtolower($this->author_namespace.'/'.$this->namespace);
    }

    protected function validateNamespacePath($namespace)
    {
        $namespace = trim($namespace);
        return strlen($namespace) && preg_match('/^[a-z]+[a-z0-9]+$/i', $namespace);
    }
}