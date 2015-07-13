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
        $filePath = self::pluginSettingsFileExists($pluginCode);
        if ($filePath === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.plugin.error_settings_not_editable'));
        }

        list($authorNamespace, $namespace) = $this->codeToNamespaces($pluginCode);
        $this->author_namespace = $authorNamespace;
        $this->namespace = $namespace;

        return parent::load($filePath);
    }

    public static function pluginSettingsFileExists($pluginCode)
    {
        $filePath = File::symbolizePath(self::codeToFilePath($pluginCode));
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
        return '$/'.$this->getPluginPath().'/plugin.yaml';
    }

    protected static function validateNamespacePath($namespace)
    {
        $namespace = trim($namespace);
        return strlen($namespace) && preg_match('/^[a-z]+[a-z0-9]+$/i', $namespace);
    }

    protected function getPluginPath()
    {
        if (!self::validateNamespacePath($this->author_namespace) || !self::validateNamespacePath($this->namespace)) {
            throw new SystemException('Invalid plugin or author namespace');
        }

        return strtolower($this->author_namespace.'/'.$this->namespace);
    }

    protected static function codeToFilePath($pluginCode)
    {
        list($authorNamespace, $namespace) = self::codeToNamespaces($pluginCode);
       
        $obj = new self();
        $obj->author_namespace = $authorNamespace;
        $obj->namespace = $namespace;

        return $obj->getFilePath();
    }

    protected static function codeToNamespaces($pluginCode)
    {
        $pluginCodeParts = explode('.', $pluginCode);
        if (count($pluginCodeParts) !== 2) {
            throw new ApplicationException('Invalid plugin code.');
        }

        list($authorNamespace, $namespace) = $pluginCodeParts;

        if (!self::validateNamespacePath($authorNamespace) || !self::validateNamespacePath($namespace)) {
            throw new SystemException('Invalid plugin code.');
        }

        return [
            $authorNamespace,
            $namespace
        ];
    }
}