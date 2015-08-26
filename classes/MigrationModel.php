<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use System\Classes\UpdateManager;
use ApplicationException;
use SystemException;
use Exception;
use Validator;
use Lang;
use File;
use Yaml;

/**
 * Manages plugin migrations
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class MigrationModel extends BaseModel
{
    /**
     * @var string Migration version string
     */
    public $version;

    /**
     * @var string The migration description
     */
    public $description;

    /**
     * @var string The migration PHP code string
     */
    public $code;

    /**
     * @var string The migration script file name.
     * Currently only migrations with a single (or none) script file are supported
     * by Builder editors.
     */
    public $scriptFileName;

    protected static $fillable = [
        'version',
        'description',
        'code'
    ];

    protected $validationRules = [
        'version' => ['required', 'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/', 'uniqueVersion'],
        'description' => ['required'],
        'code' => ['required'],
        'scriptFileName' => ['regex:/^[a-z]+[a-z0-9_]+$/']
    ];

    public function validate()
    {
        $this->validationMessages = [
            'version.regex' => Lang::get('rainlab.builder::lang.migration.error_version_invalid'),
            'version.unique_version' => Lang::get('rainlab.builder::lang.migration.error_version_exists'),
            'scriptFileName.regex' => Lang::get('rainlab.builder::lang.migration.error_script_filename_invalid')
        ];

        $versionInformation = $this->getPluginVersionInformation();

        Validator::extend('uniqueVersion', function($attribute, $value, $parameters) use ($versionInformation) {
            return !array_key_exists($value, $versionInformation);
        });

        return parent::validate();
    }

    public function getNextVersion()
    {
        $versionInformation = $this->getPluginVersionInformation();

        if (!count($versionInformation)) {
            return '1.0.0';
        }

        $versions = array_keys($versionInformation);
        $latestVersion = end($versions);

        $versionNumbers = [];
        if (!preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)$/', $latestVersion, $versionNumbers)) {
            throw new SystemException(sprintf('Cannot parse the latest plugin version number: %s.', $latestVersion));
        }

        return $versionNumbers[1].'.'.$versionNumbers[2].'.'.($versionNumbers[3]+1);
    }

    /**
     * Saves the migration and applies all outstanding migrations for the plugin.
     */
    public function save()
    {
        $this->validate();

        if ($this->isNewModel()) {
            $this->makeScriptFileNameUnique();
        }

        $this->saveScriptFile();

        try {
            if ($this->isNewModel()) {
                $originalVersionData = $this->insertVersion();
            } else {
                $originalVersionData = $this->updateVersion();
            }
        } catch (Exception $ex) {
            // Remove the script file, but don't rollback 
            // the version.yaml.
            $this->rollbackSaving(null);

            throw $ex;
        }

        try {
            UpdateManager::instance()->update();
        } catch (Exception $ex) {
            // Remove the script file, but and rollback 
            // the version.yaml.
            $this->rollbackSaving($originalVersionData);

            throw $ex;
        }
    }

    protected function saveScriptFile()
    {
        $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

        if (!File::put($scriptFilePath, $this->code)) {
            throw new SystemException(sprintf('Error saving file %s', $scriptFilePath));
        }

        @File::chmod($scriptFilePath);
    }

    protected function removeScriptFile()
    {
        $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

        // Using unlink instead of File::remove() 
        // is safer here.
        @unlink($scriptFilePath);
    }

    protected function rollbackSaving($originalVersionData)
    {
        if ($originalVersionData) {
            $this->rollbackVersionFile($originalVersionData);
        }

        if ($this->isNewModel()) {
            $this->removeScriptFile();
        }
    }

    protected function insertVersion()
    {
        $versionFilePath = $this->getPluginUpdatesPath('version.yaml');

        $versionInformation = $this->getPluginVersionInformation();
        if (!$versionInformation) {
            $versionInformation = [];
        }

        $originalFileContents = File::get($versionFilePath);
        if (!$originalFileContents) {
            throw new SystemException(sprintf('Error loading file %s', $versionFilePath));
        }

        $versionInformation[$this->version] = [
            $this->description,
            $this->scriptFileName.'.php'
        ];

        $dumper = new YamlDumper();
        $yamlData = $dumper->dump($versionInformation, 20, 0, false, true);

        if (!File::put($versionFilePath, $yamlData)) {
            throw new SystemException(sprintf('Error saving file %s', $versionFilePath));
        }

        @File::chmod($versionFilePath);

        return $originalFileContents;
    }

    protected function rollbackVersionFile($fileData)
    {
        $versionFilePath = $this->getPluginUpdatesPath('version.yaml');
        File::put($versionFilePath, $fileData);
    }

    protected function makeScriptFileNameUnique()
    {
        $updatesPath = $this->getPluginUpdatesPath();
        $baseFileName = $fileName = $this->scriptFileName.'_'.date('YmdHis');

        $counter = 2;
        while (File::isFile($updatesPath.'/'.$fileName.'.php')) {
            $fileName = $baseFileName.'_'.$counter;
            $counter++;
        }

        return $this->scriptFileName = $fileName;
    }

    protected function getPluginUpdatesPath($fileName = null)
    {
        $pluginCodeObj = $this->getPluginCodeObj();

        $filePath = '$/'.$pluginCodeObj->toFilesystemPath().'/updates';
        $filePath = File::symbolizePath($filePath);

        if ($fileName !== null) {
            return $filePath .= '/'.$fileName;
        }

        return $filePath;
    }

    protected function getPluginVersionInformation()
    {
        $filePath = $this->getPluginUpdatesPath('version.yaml');

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

        return $versionInfo;
    }
}