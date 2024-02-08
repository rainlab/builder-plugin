<?php namespace RainLab\Builder\Models;

use File;
use Lang;
use Config;
use Request;
use Cms\Classes\Theme;
use Cms\Helpers\File as FileHelper;
use ApplicationException;
use ValidationException;

/**
 * CodeFileModel for plugin code files
 *
 * @package october\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class CodeFileModel extends BaseModel
{
    /**
     * @var \Cms\Classes\Theme A reference to the CMS theme containing the object.
     */
    protected $theme;

    /**
     * @var string The plugin folder name.
     */
    protected $dirName = 'plugins';

    /**
     * @var string Specifies the file name corresponding the CMS object.
     */
    public $fileName;

    /**
     * @var string originalFileName specifies the file name, the CMS object was loaded from.
     */
    protected $originalFileName = null;

    /**
     * @var string Last modified time.
     */
    public $mtime;

    /**
     * @var string The entire file content.
     */
    public $content;

    /**
     * @var array The attributes that are mass assignable.
     */
    protected static $fillable = [
        'fileName',
        'content'
    ];

    /**
     * @var array Allowable file extensions.
     */
    protected $allowedExtensions = [];

    /**
     * @var bool Indicates if the model exists.
     */
    public $exists = false;

    /**
     * Creates an instance of the object and associates it with a CMS theme.
     * @param \Cms\Classes\Theme $theme Specifies the theme the object belongs to.
     */
    public function __construct()
    {
        $this->allowedExtensions = self::getEditableExtensions();
    }

    /**
     * load a single template by its file name.
     *
     * @param  string $fileName
     * @return mixed|static
     */
    public function load($fileName)
    {
        $filePath = $this->getFilePath($fileName);

        if (!File::isFile($filePath)) {
            return null;
        }

        if (($content = @File::get($filePath)) === false) {
            return null;
        }

        $this->fileName = $fileName;
        $this->originalFileName = $fileName;
        $this->mtime = File::lastModified($filePath);
        $this->content = $content;
        $this->exists = true;

        return $this;
    }

    /**
     * Sets the object attributes.
     * @param array $attributes A list of attributes to set.
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (!in_array($key, static::$fillable)) {
                throw new ApplicationException(Lang::get(
                    'cms::lang.cms_object.invalid_property',
                    ['name' => $key]
                ));
            }

            $this->$key = $value;
        }
    }

    /**
     * Saves the object to the disk.
     */
    public function save()
    {
        $this->validateFileName();

        $fullPath = $this->getFilePath();

        if (File::isFile($fullPath) && $this->originalFileName !== $this->fileName) {
            throw new ApplicationException(Lang::get(
                'cms::lang.cms_object.file_already_exists',
                ['name' => $this->fileName]
            ));
        }

        $dirPath = base_path($this->dirName);
        if (!file_exists($dirPath) || !is_dir($dirPath)) {
            if (!File::makeDirectory($dirPath, 0777, true, true)) {
                throw new ApplicationException(Lang::get(
                    'cms::lang.cms_object.error_creating_directory',
                    ['name' => $dirPath]
                ));
            }
        }

        if (($pos = strpos($this->fileName, '/')) !== false) {
            $dirPath = dirname($fullPath);

            if (!is_dir($dirPath) && !File::makeDirectory($dirPath, 0777, true, true)) {
                throw new ApplicationException(Lang::get(
                    'cms::lang.cms_object.error_creating_directory',
                    ['name' => $dirPath]
                ));
            }
        }

        $newFullPath = $fullPath;
        if (@File::put($fullPath, $this->content) === false) {
            throw new ApplicationException(Lang::get(
                'cms::lang.cms_object.error_saving',
                ['name' => $this->fileName]
            ));
        }

        if (strlen($this->originalFileName) && $this->originalFileName !== $this->fileName) {
            $fullPath = $this->getFilePath($this->originalFileName);

            if (File::isFile($fullPath)) {
                @unlink($fullPath);
            }
        }

        clearstatcache();

        $this->mtime = @File::lastModified($newFullPath);
        $this->originalFileName = $this->fileName;
        $this->exists = true;
    }

    /**
     * delete
     */
    public function delete()
    {
        $fileName = Request::input('fileName');
        $fullPath = $this->getFilePath($fileName);

        $this->validateFileName($fileName);

        if (File::exists($fullPath)) {
            if (!@File::delete($fullPath)) {
                throw new ApplicationException(Lang::get(
                    'cms::lang.asset.error_deleting_file',
                    ['name' => $fileName]
                ));
            }
        }
    }

    /**
     * validateFileName validates the supplied filename, extension and path.
     * @param string $fileName
     */
    protected function validateFileName($fileName = null)
    {
        if ($fileName === null) {
            $fileName = $this->fileName;
        }

        $fileName = trim($fileName);

        if (!strlen($fileName)) {
            throw new ValidationException(['fileName' =>
                Lang::get('cms::lang.cms_object.file_name_required', [
                    'allowed' => implode(', ', $this->allowedExtensions),
                    'invalid' => pathinfo($fileName, PATHINFO_EXTENSION)
                ])
            ]);
        }

        if (!FileHelper::validateExtension($fileName, $this->allowedExtensions, false)) {
            throw new ValidationException(['fileName' =>
                Lang::get('cms::lang.cms_object.invalid_file_extension', [
                    'allowed' => implode(', ', $this->allowedExtensions),
                    'invalid' => pathinfo($fileName, PATHINFO_EXTENSION)
                ])
            ]);
        }

        if (!FileHelper::validatePath($fileName, null)) {
            throw new ValidationException(['fileName' =>
                Lang::get('cms::lang.cms_object.invalid_file', [
                    'name' => $fileName
                ])
            ]);
        }
    }

    /**
     * getFileName returns the file name.
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * getFilePath returns the absolute file path.
     * @param string $fileName Specifies the file name to return the path to.
     * @return string
     */
    public function getFilePath($fileName = null)
    {
        if ($fileName === null) {
            $fileName = $this->fileName;
        }

        $pluginPath = $this->getPluginCodeObj()->toFilesystemPath();

        return base_path($this->dirName.'/'.$pluginPath.'/'.$fileName);
    }

    /**
     * getEditableExtensions returns a list of editable asset extensions.
     * The list can be overridden with the cms.editableAssetTypes configuration option.
     * @return array
     */
    public static function getEditableExtensions()
    {
        $defaultTypes = ['js', 'jsx', 'css', 'sass', 'scss', 'less', 'php', 'htm', 'html', 'yaml', 'md', 'txt'];

        $configTypes = Config::get('rainlab.builder::editable_code_types');
        if (!$configTypes) {
            return $defaultTypes;
        }

        return $configTypes;
    }
}
