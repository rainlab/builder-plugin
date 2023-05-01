<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use Yaml;
use File;
use RainLab\Builder\Models\MigrationModel;
use ApplicationException;

/**
 * HasVersionFile
 */
trait HasVersionFile
{
    /**
     * generateVersionUpdate
     */
    protected function generateVersionUpdate()
    {
        $versionFilePath = $this->sourceModel->getPluginFilePath('updates/version.yaml');

        $versionInformation = $this->sourceModel->getPluginVersionInformation();

        $nextVersion = $this->getNextVersion();

        foreach ($this->migrationScripts as $scriptName => $comment) {
            $versionInformation[$nextVersion] = [
                $comment,
                $scriptName
            ];

            $nextVersion = $this->getNextVersion($nextVersion);
        }

        // Add "v" to the version information
        $versionInformation = $this->normalizeVersions((array) $versionInformation);

        $yamlData = Yaml::render($versionInformation);

        if (!File::put($versionFilePath, $yamlData)) {
            throw new ApplicationException(sprintf('Error saving file %s', $versionFilePath));
        }

        @File::chmod($versionFilePath);
    }

    /**
     * getNextVersion returns the next version for this plugin
     */
    protected function getNextVersion($fromVersion = null)
    {
        if ($fromVersion) {
            $parts = explode('.', $fromVersion);

            $parts[count($parts)-1]++;

            return implode('.', $parts);
        }

        $migration = new MigrationModel;

        $migration->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        return $migration->getNextVersion();
    }

    /**
     * normalizeVersions checks some versions start with v and others not
     */
    protected function normalizeVersions(array $versions): array
    {
        $result = [];
        foreach ($versions as $key => $value) {
            $version = rtrim(ltrim((string) $key, 'v'), '.');
            $result['v'.$version] = $value;
        }
        return $result;
    }
}
