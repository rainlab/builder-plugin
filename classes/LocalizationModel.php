<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use SystemException;
use DirectoryIterator;
use ValidationException;
use Yaml;
use Exception;
use Config;
use Lang;
use File;

/**
 * Represents and manages plugin localization files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class LocalizationModel extends BaseModel
{
    public $strings;

    public $language;

    public $originalLanguage;

    protected static $fillable = [
        'strings',
        'language'
    ];

    protected $validationRules = [
        'language' => ['required', 'regex:/^[a-z0-9\.\-]+$/i']
    ];

    protected $originalStringArray = [];

    public function load($language)
    {
        $this->language = $language;

        $this->originalLanguage = $language;

        $filePath = $this->getFilePath();

        if (!File::isFile($filePath)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.localization.error_cant_load_file'));
        }

        if (!$this->validateFileContents($filePath)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.localization.error_bad_localization_file_contents'));
        }

        $strings = include($filePath);
        if (!is_array($strings)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.localization.error_file_not_array'));
        }

        $this->originalStringArray = $strings;

        if (count($strings) > 0) {
            $dumper = new YamlDumper();
            $this->strings = $dumper->dump($strings, 20, 0, false, true);
        }
        else {
            $this->strings = '';
        }
        
        $this->exists = true;
    }

    public static function initModel($pluginCode, $language)
    {
        $model = new self();
        $model->setPluginCode($pluginCode);
        $model->language = $language;

        return $model;
    }

    public function save()
    {
        $data = $this->modelToLanguageFile();
        $this->validate();

        $filePath = File::symbolizePath($this->getFilePath());
        $isNew = $this->isNewModel();

        if (File::isFile($filePath)) {
            if ($isNew || $this->originalLanguage != $this->language) {
                throw new ValidationException(['fileName' => Lang::get('rainlab.builder::lang.common.error_file_exists', ['path'=>$this->language.'/'.basename($filePath)])]);
            }
        }

        $fileDirectory = dirname($filePath);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_make_dir', ['name'=>$fileDirectory]));
            }
        }

        if (@File::put($filePath, $data) === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.localization.save_error', ['name'=>$filePath]));
        }

        @File::chmod($filePath);

        if (!$this->isNewModel() && strlen($this->originalLanguage) > 0 && $this->originalLanguage != $this->language) {
            $this->originalFilePath = $this->getFilePath($this->originalLanguage);
            @File::delete($this->originalFilePath);
        }

        $this->originalLanguage = $this->language;
        $this->exists = true;
    }

    public function deleteModel()
    {
        if ($this->isNewModel()) {
            throw new ApplicationException('Cannot delete language file which is not saved yet.');
        }

        $filePath = File::symbolizePath($this->getFilePath());
        if (File::isFile($filePath)) {
            if (!@unlink($filePath)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.localization.error_delete_file'));
            }
        }
    }

    public function initContent()
    {
        $templatePath = '$/rainlab/builder/classes/localizationmodel/templates/lang.php';
        $templatePath = File::symbolizePath($templatePath);

        $strings = include($templatePath);
        $dumper = new YamlDumper();
        $this->strings = $dumper->dump($strings, 20, 0, false, true);
    }

    public static function listPluginLanguages($pluginCodeObj)
    {
        $languagesDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/lang';

        $languagesDirectoryPath = File::symbolizePath($languagesDirectoryPath);

        if (!File::isDirectory($languagesDirectoryPath)) {
            return [];
        }

        $result = [];
        foreach (new DirectoryIterator($languagesDirectoryPath) as $fileInfo) {
            if (!$fileInfo->isDir() || $fileInfo->isDot()) {
                continue;
            }

            $langFilePath = $fileInfo->getPathname().'/lang.php';

            if (File::isFile($langFilePath)) {
                $result[] = $fileInfo->getFilename();
            }
        }

        return $result;
    }

    public function copyStringsFrom($destinationText, $sourceLanguageCode)
    {
        $sourceLanguageModel = new self();
        $sourceLanguageModel->setPluginCodeObj($this->getPluginCodeObj());
        $sourceLanguageModel->load($sourceLanguageCode);

        $srcArray = $sourceLanguageModel->getOriginalStringsArray();

        $languageMixer = new LanguageMixer();
        
        return $languageMixer->addStringsFromAnotherLanguage($destinationText, $srcArray);
    }

    public function getOriginalStringsArray()
    {
        return $this->originalStringArray;
    }

    public function createStringAndSave($stringKey, $stringValue)
    {
        $stringKey = trim($stringKey, '.');

        if (!strlen($stringKey)) {
            throw new ValidationException(['key' => Lang::get('rainlab.builder::lang.localization.string_key_is_empty')]);
        }

        if (!strlen($stringValue)) {
            throw new ValidationException(['value' => Lang::get('rainlab.builder::lang.localization.string_value_is_empty')]);
        }

        $originalStringArray = $this->getOriginalStringsArray();
        $languagePrefix = strtolower($this->getPluginCodeObj()->toCode()).'::lang.';

        $existingStrings = self::convertToStringsArray($originalStringArray, $languagePrefix);
        if (array_key_exists($languagePrefix.$stringKey, $existingStrings)) {
            throw new ValidationException(['key' => Lang::get('rainlab.builder::lang.localization.string_key_exists')]);
        }

        $existingSections = self::convertToSectionsArray($originalStringArray);
        if (array_key_exists($stringKey.'.', $existingSections)) {
            throw new ValidationException(['key' => Lang::get('rainlab.builder::lang.localization.string_key_exists')]);
        }

        $sectionArray = [];
        self::createStringSections($sectionArray, $stringKey, $stringValue) ;

        $this->checkKeyWritable($stringKey, $existingStrings, $languagePrefix);
        $newStrings = LanguageMixer::arrayMergeRecursive($originalStringArray, $sectionArray);

        $dumper = new YamlDumper();
        $this->strings = $dumper->dump($newStrings, 20, 0, false, true);

        $this->save();

        return $languagePrefix.$stringKey;
    }

    public static function getDefaultLanguage()
    {
        $language = Config::get('app.locale');

        if (!$language) {
            throw new ApplicationException('The default language is not defined in the application configuration (app.locale).');
        }

        return $language;
    }

    public static function getPluginRegistryData($pluginCode, $subtype)
    {
        $defaultLanguage = self::getDefaultLanguage();

        $model = new self();
        $model->setPluginCode($pluginCode);
        $model->language = $defaultLanguage;

        $filePath = $model->getFilePath();
        if (!File::isFile($filePath)) {
            return [];
        }

        $model->load($defaultLanguage);

        $array = $model->getOriginalStringsArray();
        $languagePrefix = strtolower($model->getPluginCodeObj()->toCode()).'::lang.';

        if ($subtype !== 'sections') {
            return self::convertToStringsArray($array, $languagePrefix);
        }

        return self::convertToSectionsArray($array);
    }

    public static function languageFileExists($pluginCode, $language)
    {
        $model = new self();
        $model->setPluginCode($pluginCode);
        $model->language = $language;

        $filePath = $model->getFilePath();
        return File::isFile($filePath);
    }

    protected static function createStringSections(&$arr, $path, $value) {
        $keys = explode('.', $path);

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }

    protected static function convertToStringsArray($stringsArray, $prefix, $currentKey = '')
    {
        $result = [];

        foreach ($stringsArray as $key=>$value) {
            $newKey = strlen($currentKey) ? $currentKey.'.'.$key : $key;

            if (is_scalar($value)) {
                $result[$prefix.$newKey] = $value;
            }
            else {
                $result = array_merge($result, self::convertToStringsArray($value, $prefix, $newKey));
            }
        }

        return $result;
    }

    protected static function convertToSectionsArray($stringsArray, $currentKey = '')
    {
        $result = [];

        foreach ($stringsArray as $key=>$value) {
            $newKey = strlen($currentKey) ? $currentKey.'.'.$key : $key;

            if (is_scalar($value)) {
                $result[$currentKey.'.'] = $currentKey.'.';
            }
            else {
                $result = array_merge($result, self::convertToSectionsArray($value, $newKey));
            }
        }

        return $result;
    }

    protected function validateLanguage($language)
    {
        return preg_match('/^[a-z0-9\.\-]+$/i', $language);
    }

    protected function getFilePath($language = null)
    {
        if ($language === null) {
            $language = $this->language;
        }

        $language = trim($language);

        if (!strlen($language)) {
            throw new SystemException('The form model language is not set.');
        }

        if (!$this->validateLanguage($language)) {
            throw new SystemException('Invalid language file name: '.$language);
        }

        $path = $this->getPluginCodeObj()->toPluginDirectoryPath().'/lang/'.$language.'/lang.php';
        return File::symbolizePath($path);
    }

    protected function modelToLanguageFile()
    {
        $this->strings = trim($this->strings);

        if (!strlen($this->strings)) {
            return "<?php return [\n];";
        }

        try {
            $data = $this->getSanitizedPHPStrings(Yaml::parse($this->strings));

            $phpData = var_export($data, true);
            $phpData = preg_replace('/^(\s+)\),/m', '$1],', $phpData);
            $phpData = preg_replace('/^(\s+)array\s+\(/m', '$1[', $phpData);
            $phpData = preg_replace_callback('/^(\s+)/m', function($matches) {
                return str_repeat($matches[1], 2); // Increase indentation
            }, $phpData);
            $phpData = preg_replace('/\n\s+\[/m', '[', $phpData);
            $phpData = preg_replace('/^array\s\(/', '[', $phpData);
            $phpData = preg_replace('/^\)\Z/m', ']', $phpData);

            return "<?php return ".$phpData.";";
        } 
        catch (Exception $ex) {
            throw new ApplicationException(sprintf('Cannot parse the YAML content: %s', $ex->getMessage()));
        }
    }

    protected function validateFileContents($path)
    {
        $fileContents = File::get($path);

        $stream = new PhpSourceStream($fileContents);

        $invalidTokens = [
            T_CLASS,
            T_FUNCTION,
            T_INCLUDE,
            T_INCLUDE_ONCE,
            T_REQUIRE,
            T_REQUIRE_ONCE,
            T_EVAL,
            T_ECHO,
            T_GOTO,
            T_HALT_COMPILER,
            T_STRING // Unescaped strings - function names, etc.
        ];

        while ($stream->forward()) {
            $tokenCode = $stream->getCurrentCode();

            if (in_array($tokenCode, $invalidTokens)) {
                return false;
            }
        }

        return true;
    }

    protected function getSanitizedPHPStrings($strings)
    {
        array_walk_recursive($strings, function(&$item, $key){
            if (!is_scalar($item)) {
                return;
            }

            // In YAML single quotes are escaped with two single quotes
            // http://yaml.org/spec/current.html#id2534365
            $item = str_replace("''", "'", $item); 
        });

        return $strings;
    }

    protected function checkKeyWritable($stringKey, $existingStrings, $languagePrefix)
    {
        $sectionList = explode('.', $stringKey);

        $lastElement = array_pop($sectionList);
        while (strlen($lastElement)) {
            if (count($sectionList) > 0) {
                $fullKey = implode('.', $sectionList).'.'.$lastElement;
            }
            else {
                $fullKey = $lastElement;
            }

            if (array_key_exists($languagePrefix.$fullKey, $existingStrings)) {
                throw new ValidationException(['key' => Lang::get('rainlab.builder::lang.localization.string_key_is_a_string', ['key'=>$fullKey])]);
            }

            $lastElement = array_pop($sectionList);
        }
    }
}