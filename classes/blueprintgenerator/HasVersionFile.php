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
            $versionInformation['v'.$nextVersion] = [
                $comment,
                $scriptName
            ];

            $nextVersion = $this->getNextVersion($nextVersion);
        }

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
}
