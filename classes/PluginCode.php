<?php namespace RainLab\Builder\Classes;

use Db;
use ApplicationException;

/**
 * PluginCode represents a plugin code and provides basic code operations.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginCode
{
    /**
     * @var string authorCode
     */
    protected $authorCode;

    /**
     * @var string pluginCode
     */
    protected $pluginCode;

    /**
     * __construct
     */
    public function __construct($pluginCodeStr)
    {
        $codeParts = explode('.', $pluginCodeStr);
        if (count($codeParts) !== 2) {
            throw new ApplicationException(sprintf('Invalid plugin code: %s', $pluginCodeStr));
        }

        list($authorCode, $pluginCode) = $codeParts;

        if (!$this->validateCodeWord($authorCode) || !$this->validateCodeWord($pluginCode)) {
            throw new ApplicationException(sprintf('Invalid plugin code: %s', $pluginCodeStr));
        }

        $this->authorCode = trim($authorCode);
        $this->pluginCode = trim($pluginCode);
    }

    /**
     * createFromNamespace
     */
    public static function createFromNamespace($namespace)
    {
        $namespaceParts = explode('\\', $namespace);
        if (count($namespaceParts) < 2) {
            throw new ApplicationException('Invalid plugin namespace value.');
        }

        $authorCode = $namespaceParts[0];
        $pluginCode = $namespaceParts[1];

        return new self($authorCode.'.'.$pluginCode);
    }

    /**
     * toPluginNamespace
     */
    public function toPluginNamespace()
    {
        return $this->authorCode.'\\'.$this->pluginCode;
    }

    /**
     * toUrl
     */
    public function toUrl()
    {
        return strtolower($this->authorCode).'/'.strtolower($this->pluginCode);
    }

    /**
     * toUpdatesNamespace
     */
    public function toUpdatesNamespace()
    {
        return $this->toPluginNamespace().'\\Updates';
    }

    /**
     * toFilesystemPath
     */
    public function toFilesystemPath()
    {
        return strtolower($this->authorCode.'/'.$this->pluginCode);
    }

    /**
     * toCode
     */
    public function toCode()
    {
        return $this->authorCode.'.'.$this->pluginCode;
    }

    /**
     * toPluginFilePath
     */
    public function toPluginFilePath()
    {
        return '$/'.$this->toFilesystemPath().'/plugin.yaml';
    }

    /**
     * toPluginInformationFilePath
     */
    public function toPluginInformationFilePath()
    {
        return '$/'.$this->toFilesystemPath().'/Plugin.php';
    }

    /**
     * toPluginDirectoryPath
     */
    public function toPluginDirectoryPath()
    {
        return '$/'.$this->toFilesystemPath();
    }

    /**
     * toDatabasePrefix
     */
    public function toDatabasePrefix($dbPrefix = false)
    {
        $builderPrefix = strtolower($this->authorCode.'_'.$this->pluginCode);

        if ($dbPrefix) {
            return Db::getTablePrefix() . $builderPrefix;
        }

        return $builderPrefix;
    }

    /**
     * toPermissionPrefix
     */
    public function toPermissionPrefix()
    {
        return strtolower($this->authorCode.'.'.$this->pluginCode);
    }

    /**
     * getAuthorCode
     */
    public function getAuthorCode()
    {
        return $this->authorCode;
    }

    /**
     * getPluginCode
     */
    public function getPluginCode()
    {
        return $this->pluginCode;
    }

    /**
     * validateCodeWord
     */
    protected function validateCodeWord($str)
    {
        $str = trim($str);
        return strlen($str) && preg_match('/^[a-z]+[a-z0-9]+$/i', $str);
    }
}
