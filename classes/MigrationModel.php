<?php namespace RainLab\Builder\Classes;

use RainLab\Builder\Models\Settings as PluginSettings;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use October\Rain\Parse\Template as TextParser;
use System\Classes\VersionManager;
use System\Classes\UpdateManager;
use ApplicationException;
use ValidationException;
use SystemException;
use Exception;
use Validator;
use Lang;
use File;
use Yaml;
use Str;

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

    protected $originalVersion;

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
        $isNewModel = $this->isNewModel();

        $this->validationMessages = [
            'version.regex' => Lang::get('rainlab.builder::lang.migration.error_version_invalid'),
            'version.unique_version' => Lang::get('rainlab.builder::lang.migration.error_version_exists'),
            'scriptFileName.regex' => Lang::get('rainlab.builder::lang.migration.error_script_filename_invalid')
        ];

        $versionInformation = $this->getPluginVersionInformation();

        Validator::extend('uniqueVersion', function($attribute, $value, $parameters) use ($versionInformation, $isNewModel) {
            if ($isNewModel || $this->version != $this->originalVersion) {
                return !array_key_exists($value, $versionInformation);
            }
            return true;
        });

        if (!$isNewModel && $this->version != $this->originalVersion && $this->isApplied()) {
            throw new ValidationException([
                'version' => Lang::get('rainlab.builder::lang.migration.error_cannot_change_version_number')
            ]);
        }

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
    public function save($executeOnSave = true)
    {
        $this->validate();

        if ($this->isNewModel()) {
            $this->makeScriptFileNameUnique();
        }

        $originalFileContents = $this->saveScriptFile();

        try {
            $originalVersionData = $this->insertOrUpdateVersion();
        } catch (Exception $ex) {
            // Remove the script file, but don't rollback 
            // the version.yaml.
            $this->rollbackSaving(null, $originalFileContents);

            throw $ex;
        }

        try {
            if ($executeOnSave) {
// TODO: stop updating the plugin on this exactly version - don't run all pending migrations here
                UpdateManager::instance()->updatePlugin($this->getPluginCodeObj()->toCode());
            }
        } catch (Exception $ex) {
            // Remove the script file, but and rollback 
            // the version.yaml.
            $this->rollbackSaving($originalVersionData, $originalFileContents);

            throw $ex;
        }

        $this->originalVersion = $this->version;
        $this->exists = true;
    }

    public function load($versionNumber)
    {
        $versionNumber = trim($versionNumber);

        if (!strlen($versionNumber)) {
            throw new ApplicationException('Cannot load the the version model - the version number should not be empty.');
        }

        $pluginVersions = $this->getPluginVersionInformation();
        if (!array_key_exists($versionNumber, $pluginVersions)) {
            throw new ApplicationException('The requested version does not exist in the version information file.');
        }

        $this->version = $versionNumber;
        $this->originalVersion = $this->version;
        $this->exists = true;

        $versionInformation = $pluginVersions[$versionNumber];
        if (!is_array($versionInformation)) {
            $this->description = $versionInformation;
        } 
        else {
            $cnt = count($versionInformation);

            if ($cnt > 2) {
                throw new ApplicationException('The requested version cannot be edited with Builder as it refers to multiple PHP files.');
            }

            if ($cnt > 0) {
                $this->description = $versionInformation[0];
            }

            if ($cnt > 1) {
                $this->scriptFileName = pathinfo(trim($versionInformation[1]), PATHINFO_FILENAME);
                $this->code = $this->loadScriptFile();
            }
        }
    }

    public function initVersion($versionType)
    {
        $versionTypes = ['migration', 'seeder', 'custom'];

        if (!in_array($versionType, $versionTypes)) {
            throw new SystemException('Unknown version type.');
        }

        $this->version = $this->getNextVersion();

        $templateFiles = [
            'migration' => 'migration.php.tpl',
            'seeder' => 'seeder.php.tpl',
            'custom' => 'custom.php.tpl'
        ];

        $templatePath = '$/rainlab/builder/classes/migrationmodel/templates/'.$templateFiles[$versionType];
        $templatePath = File::symbolizePath($templatePath);

        $fileContents = File::get($templatePath);
        $scriptFileName = $versionType.str_replace('.', '-', $this->version);

        $pluginCodeObj = $this->getPluginCodeObj();
        $this->code = TextParser::parse($fileContents, [
            'className' => Str::studly($scriptFileName),
            'namespace' => $pluginCodeObj->toPluginNamespace()
        ]);

        $this->scriptFileName = $scriptFileName;
    }

    protected function saveScriptFile()
    {
        $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

        $originalFileContents = null;
        if (File::isFile($scriptFilePath)) {
            $originalFileContents = File::get($scriptFilePath);
        }

        if (!File::put($scriptFilePath, $this->code)) {
            throw new SystemException(sprintf('Error saving file %s', $scriptFilePath));
        }

        @File::chmod($scriptFilePath);

        return $originalFileContents;
    }

    protected function loadScriptFile()
    {
        $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

        if (!File::isFile($scriptFilePath)) {
            throw new ApplicationException(sprintf('Version file %s is not found.', $scriptFilePath));
        }

        return File::get($scriptFilePath);
    }

    protected function removeScriptFile()
    {
        $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

        // Using unlink instead of File::remove() 
        // is safer here.
        @unlink($scriptFilePath);
    }

    protected function rollbackScriptFile($fileContents)
    {
        $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

        @File::put($scriptFilePath, $fileContents);
    }

    protected function rollbackSaving($originalVersionData, $originalScriptFileContents)
    {
        if ($originalVersionData) {
            $this->rollbackVersionFile($originalVersionData);
        }

        if ($this->isNewModel()) {
            $this->removeScriptFile();
        }
        else {
            $this->rollbackScriptFile($originalScriptFileContents);
        }
    }

    protected function insertOrUpdateVersion()
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

        if (!$this->isNewModel() && $this->version != $this->originalVersion) {
            if (array_key_exists($this->originalVersion, $versionInformation)) {
                unset($versionInformation[$this->originalVersion]);
            }
        }

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
        $versionObj = new PluginVersion();
        return $versionObj->getPluginVersionInformation($this->getPluginCodeObj());
    }

    protected function isApplied()
    {
        if ($this->isNewModel()) {
            return false;
        }

        $versionManager = VersionManager::instance();
        $unappliedVersions = $versionManager->listNewVersions($this->pluginCodeObj->toCode());

        return !array_key_exists($this->originalVersion, $unappliedVersions);
    }
}