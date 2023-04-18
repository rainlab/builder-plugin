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

        $nextVersion = $this->getNextVersion();

        $versionInformation = $this->sourceModel->getPluginVersionInformation();

        $versionInformation[$nextVersion] = [
            "Imported Blueprints from Tailor"
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
     * getNextVersion returns the next version for this plugin
     */
    protected function getNextVersion()
    {
        $migration = new MigrationModel;

        $migration->setPluginCodeObj($this->sourceModel->getPluginCodeObj());

        return $migration->getNextVersion();
    }
}
