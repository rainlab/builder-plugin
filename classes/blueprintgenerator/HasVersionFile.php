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
        $blueprintName = $this->getConfig('name', 'Unknown');

        $versionFilePath = $this->sourceModel->getPluginFilePath('updates/version.yaml');

        $nextVersion = $this->getNextVersion();

        $versionInformation = $this->sourceModel->getPluginVersionInformation();

        $versionInformation[$nextVersion] = [
            "Created {$blueprintName} Tables"
        ];

        foreach ($this->migrationScripts as $scriptName) {
            $versionInformation[$nextVersion][] = $scriptName;
        }

        $yamlData = Yaml::render($versionInformation);

        if (!File::put($versionFilePath, $yamlData)) {
            throw new ApplicationException(sprintf('Error saving file %s', $versionFilePath));
        }

        @File::chmod($versionFilePath);
    }

    /**
     * getNextVersion
     */
    protected function getNextVersion()
    {
        $migration = new MigrationModel;
        $migration->setPluginCodeObj($this->sourceModel->getPluginCodeObj());
        return $migration->getNextVersion();
    }
}
