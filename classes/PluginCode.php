<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use SystemException;

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
            throw new ApplicationException('Invalid plugin code.');
        }

        list($authorCode, $pluginCode) = $codeParts;

        if (!$this->validateCodeWord($authorCode) || !$this->validateCodeWord($pluginCode)) {
            throw new ApplicationException('Invalid plugin code.');
        }

        $this->authorCode = trim($authorCode);
        $this->pluginCode = trim($pluginCode);
    }

    public function toPluginNamespace()
    {
        return $this->authorCode.'\\'.$this->pluginCode;
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

    public function toDatabasePrefix()
    {
        return strtolower($this->authorCode.'_'.$this->pluginCode);
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