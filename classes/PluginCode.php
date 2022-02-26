<?php namespace RainLab\Builder\Classes;

use Db;
use ApplicationException;

/**
 * Represents a plugin code and provides basic code operations.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginCode
{
    private $authorCode;

    private $pluginCode;

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

    public function toPluginNamespace()
    {
        return $this->authorCode.'\\'.$this->pluginCode;
    }

    public function toUrl()
    {
        return strtolower($this->authorCode).'/'.strtolower($this->pluginCode);
    }

    public function toUpdatesNamespace()
    {
        return $this->toPluginNamespace().'\\Updates';
    }

    public function toFilesystemPath()
    {
        return strtolower($this->authorCode.'/'.$this->pluginCode);
    }

    public function toCode()
    {
        return $this->authorCode.'.'.$this->pluginCode;
    }

    public function toPluginFilePath()
    {
        return '$/'.$this->toFilesystemPath().'/plugin.yaml';
    }

    public function toPluginInformationFilePath()
    {
        return '$/'.$this->toFilesystemPath().'/Plugin.php';
    }

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

    public function getAuthorCode()
    {
        return $this->authorCode;
    }

    public function getPluginCode()
    {
        return $this->pluginCode;
    }

    private function validateCodeWord($str)
    {
        $str = trim($str);
        return strlen($str) && preg_match('/^[a-z]+[a-z0-9]+$/i', $str);
    }
}
