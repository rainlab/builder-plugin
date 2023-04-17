<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use Lang;
use Yaml;
use File;
use Twig;
use Tailor\Classes\SchemaBuilder;
use RainLab\Builder\Models\MigrationModel;
use Tailor\Classes\BlueprintIndexer;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;

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
        $blueprintName = $this->activeConfig['name'] ?? 'Unknown';

        $versionFilePath = $this->sourceModel->getPluginFilePath('updates/version.yaml');

        $nextVersion = $this->getNextVersion();

        $versionInformation = $this->sourceModel->getPluginVersionInformation();

        $versionInformation[$nextVersion] = [
            "Created {$blueprintName} Tables"
        ];

        // @todo
        $versionInformation[$nextVersion][] = 'script_name.php';

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
