<?php namespace RainLab\Builder\Classes;

use ApplicationException;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use SystemException;
use DirectoryIterator;
use ValidationException;
use Yaml;
use Exception;
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

    protected static $fillable = [
        'strings',
        'language'
    ];

    protected $validationRules = [
        'language' => ['required', 'regex:/^[a-z0-9\.\-]+$/i']
    ];

    public $originalLanguage;

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

        if (count($strings) > 0) {
            $dumper = new YamlDumper();
            $this->strings = $dumper->dump($strings, 20, 0, false, true);
        }
        else {
            $this->strings = '';
        }
        
        $this->exists = true;
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
            $data = Yaml::parse($this->strings);

            $phpData = var_export($data, true);
            $phpData = preg_replace('/^(\s+)\),/m', '$1],', $phpData);
            $phpData = preg_replace('/^(\s+)array\s+\(/m', '$1[', $phpData);
            $phpData = preg_replace_callback('/^(\s+)/m', function($matches){
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
}