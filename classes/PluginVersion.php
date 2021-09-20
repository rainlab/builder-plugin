<?php namespace RainLab\Builder\Classes;

use SystemException;
use File;
use Yaml;

/**
 * Helper class for managing plugin versions
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PluginVersion extends BaseModel
{
    /**
     * getPluginVersionInformation returns version information for a plugin.
     */
    public function getPluginVersionInformation($pluginCodeObj)
    {
        $filePath = $this->getPluginUpdatesPath($pluginCodeObj, 'version.yaml');

        if (!File::isFile($filePath)) {
            throw new SystemException('Plugin version.yaml file is not found.');
        }

        $versionInfo = Yaml::parseFile($filePath);

        if (!is_array($versionInfo)) {
            $versionInfo = [];
        }

        if ($versionInfo) {
            uksort($versionInfo, function ($a, $b) {
                return version_compare($a, $b);
            });
        }

        // Normalize result
        $result = [];

        foreach ($versionInfo as $version => $info) {
            $result[$this->normalizeVersion($version)] = $info;
        }

        return $result;
    }

    /**
     * getPluginUpdatesPath
     */
    protected function getPluginUpdatesPath($pluginCodeObj, $fileName = null)
    {
        $filePath = '$/'.$pluginCodeObj->toFilesystemPath().'/updates';
        $filePath = File::symbolizePath($filePath);

        if ($fileName !== null) {
            return $filePath .= '/'.$fileName;
        }

        return $filePath;
    }

    /**
     * normalizeVersion checks some versions start with v and others not
     */
    protected function normalizeVersion($version): string
    {
        return rtrim(ltrim((string) $version, 'v'), '.');
    }
}
