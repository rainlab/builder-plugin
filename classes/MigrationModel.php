<?php namespace RainLab\Builder\Classes;

use Symfony\Component\Yaml\Dumper as YamlDumper;
use October\Rain\Parse\Template as TextParser;
use System\Classes\VersionManager;
use ApplicationException;
use ValidationException;
use SystemException;
use Exception;
use Validator;
use Lang;
use File;
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

    public $originalScriptFileName;

    protected static $fillable = [
        'version',
        'description',
        'code'
    ];

    protected $validationRules = [
        'version' => ['required', 'regex:/^[0-9]+\.[0-9]+\.[0-9]+$/', 'uniqueVersion'],
        'description' => ['required'],
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

        if (!strlen($this->scriptFileName) || !$this->isNewModel()) {
            $this->assignFileName();
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
                VersionManager::instance()->updatePlugin($this->getPluginCodeObj()->toCode(), $this->version);
            }
        }
        catch (Exception $ex) {
            // Remove the script file, and rollback 
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
                throw new ApplicationException('The requested version cannot be edited with Builder as it refers to multiple PHP scripts.');
            }

            if ($cnt > 0) {
                $this->description = $versionInformation[0];
            }

            if ($cnt > 1) {
                $this->scriptFileName = pathinfo(trim($versionInformation[1]), PATHINFO_FILENAME);
                $this->code = $this->loadScriptFile();
            }
        }

        $this->originalScriptFileName = $this->scriptFileName;
    }

    public function initVersion($versionType)
    {
        $versionTypes = ['migration', 'seeder', 'custom'];

        if (!in_array($versionType, $versionTypes)) {
            throw new SystemException('Unknown version type.');
        }

        $this->version = $this->getNextVersion();

        if ($versionType == 'custom') {
            $this->scriptFileName = null;
            return;
        }

        $templateFiles = [
            'migration' => 'migration.php.tpl',
            'seeder' => 'seeder.php.tpl'
        ];

        $templatePath = '$/rainlab/builder/classes/migrationmodel/templates/'.$templateFiles[$versionType];
        $templatePath = File::symbolizePath($templatePath);

        $fileContents = File::get($templatePath);
        $scriptFileName = $versionType.str_replace('.', '-', $this->version);

        $pluginCodeObj = $this->getPluginCodeObj();
        $this->code = TextParser::parse($fileContents, [
            'className' => Str::studly($scriptFileName),
            'namespace' => $pluginCodeObj->toUpdatesNamespace(),
            'tableNamePrefix' => $pluginCodeObj->toDatabasePrefix()
        ]);

        $this->scriptFileName = $scriptFileName;
    }

    public function makeScriptFileNameUnique()
    {
        $updatesPath = $this->getPluginUpdatesPath();
        $baseFileName = $fileName = $this->scriptFileName;

        $counter = 2;
        while (File::isFile($updatesPath.'/'.$fileName.'.php')) {
            $fileName = $baseFileName.'_'.$counter;
            $counter++;
        }

        return $this->scriptFileName = $fileName;
    }

    public function deleteModel()
    {
        if ($this->isApplied()) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.migration.error_cant_delete_applied'));
        }

        $this->deleteVersion();
        $this->removeScriptFile();
    }

    public function isApplied()
    {
        if ($this->isNewModel()) {
            return false;
        }

        $versionManager = VersionManager::instance();
        $unappliedVersions = $versionManager->listNewVersions($this->pluginCodeObj->toCode());

        return !array_key_exists($this->originalVersion, $unappliedVersions);
    }

    public function apply()
    {
        if ($this->isApplied()) {
            return;
        }

        $versionManager = VersionManager::instance();
        $versionManager->updatePlugin($this->pluginCodeObj->toCode(), $this->version);
    }

    public function rollback()
    {
        if (!$this->isApplied()) {
            return;
        }

        $versionManager = VersionManager::instance();
        $versionManager->removePlugin($this->pluginCodeObj->toCode(), $this->version);
    }

    protected function assignFileName()
    {
        $code = trim($this->code);

        if (!strlen($code)) {
            $this->scriptFileName = null;
            return;
        }

        // The file name is based on the migration class name. 
        //
        $parser = new MigrationFileParser();
        $migrationInfo = $parser->extractMigrationInfoFromSource($code);

        if (!$migrationInfo || !array_key_exists('class', $migrationInfo)) {
            throw new ValidationException([
                'code' => Lang::get('rainlab.builder::lang.migration.error_file_must_define_class')
            ]);
        }

        if (!array_key_exists('namespace', $migrationInfo)) {
            throw new ValidationException([
                'code' => Lang::get('rainlab.builder::lang.migration.error_file_must_define_namespace')
            ]);
        }

        $pluginCodeObj = $this->getPluginCodeObj();
        $pluginNamespace = $pluginCodeObj->toUpdatesNamespace();

        if ($migrationInfo['namespace'] != $pluginNamespace) {
            throw new ValidationException([
                'code' => Lang::get('rainlab.builder::lang.migration.error_namespace_mismatch', ['namespace'=>$pluginNamespace])
            ]);
        }

        $this->scriptFileName = Str::snake($migrationInfo['class']);

        // Validate that a file with the generated name does not exist yet.
        //
        if ($this->scriptFileName != $this->originalScriptFileName) {
            $fileName = $this->scriptFileName.'.php';
            $filePath = $this->getPluginUpdatesPath($fileName);

            if (File::isFile($filePath)) {
                throw new ValidationException([
                    'code' => Lang::get('rainlab.builder::lang.migration.error_migration_file_exists', ['file'=>$fileName])
                ]);
            }
        }
    }

    protected function saveScriptFile()
    {
        $originalFileContents = $this->getOriginalFileContents();

        if (strlen($this->scriptFileName)) {
            $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');

            if (!File::put($scriptFilePath, $this->code)) {
                throw new SystemException(sprintf('Error saving file %s', $scriptFilePath));
            }

            @File::chmod($scriptFilePath);
        }

        if (strlen($this->originalScriptFileName) && $this->scriptFileName != $this->originalScriptFileName) {
            $originalScriptFilePath = $this->getPluginUpdatesPath($this->originalScriptFileName.'.php');
            if (File::isFile($originalScriptFilePath)) {
                @unlink($originalScriptFilePath);
            }
        }

        return $originalFileContents;
    }

    protected function getOriginalFileContents()
    {
        if (!strlen($this->originalScriptFileName)) {
            return null;
        }

        $scriptFilePath = $this->getPluginUpdatesPath($this->originalScriptFileName.'.php');
        if (File::isFile($scriptFilePath)) {
            return File::get($scriptFilePath);
        }
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
        $scriptFilePath = $this->getPluginUpdatesPath($this->originalScriptFileName.'.php');

        @File::put($scriptFilePath, $fileContents);

        if ($this->scriptFileName != $this->originalScriptFileName) {
            $scriptFilePath = $this->getPluginUpdatesPath($this->scriptFileName.'.php');
            @unlink($scriptFilePath);
        }
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
            $this->description
        ];

        if (strlen($this->scriptFileName)) {
            $versionInformation[$this->version][] = $this->scriptFileName.'.php';
        }

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

    protected function deleteVersion()
    {
        $versionInformation = $this->getPluginVersionInformation();
        if (!$versionInformation) {
            $versionInformation = [];
        }

        if (array_key_exists($this->version, $versionInformation)) {
            unset($versionInformation[$this->version]);
        }

        $versionFilePath = $this->getPluginUpdatesPath('version.yaml');

        $dumper = new YamlDumper();
        $yamlData = $dumper->dump($versionInformation, 20, 0, false, true);

        if (!File::put($versionFilePath, $yamlData)) {
            throw new SystemException(sprintf('Error saving file %s', $versionFilePath));
        }

        @File::chmod($versionFilePath);
    }

    protected function rollbackVersionFile($fileData)
    {
        $versionFilePath = $this->getPluginUpdatesPath('version.yaml');
        File::put($versionFilePath, $fileData);
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
}