<?php namespace RainLab\Builder\Classes;

use October\Rain\Parse\Template as TextParser;
use ApplicationException;
use SystemException;
use File;
use Lang;

/**
 * Generates filesystem objects basing on a structure provided with an array 
 * and using file templates and variables. Variables in template files use
 * the Twig syntax, but processed with October\Rain\Syntax\TextParser.
 *
 * Example - generate a plugin directory containing the plugin.php file.
 * The file is created from a template, which uses a couple of variables.
 *
 * $structure = [
 *     'author',
 *     'author/plugin',
 *     'author/plugin/plugin.php' => 'plugin.php.tpl'
 * ];
 * $generator = new FilesystemGenerator('$', $structure, '$/Author/Plugin/templates/plugin');
 * 
 * $variables = [
 *     'namespace' => 'Author/Plugin'
 * ];
 * $generator->setVariables($variables);
 * $generator->generate();
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class FilesystemGenerator
{
    protected $dstPath;

    protected $structure;

    protected $variables = [];

    protected $templatesPath;

    /**
     * Initializes the object.
     * @param string $dstPath Destination path to create the filesystem objects in.
     * The path can contain filesystem symbols.
     * @param array $structure Specifies the structure as array.
     * @param string $templatesPath Path to the directory that contains file templates.
     * The parameter is required only in case any files should be created. The path can 
     * contain filesystem symbols.
     */
    public function __construct($dstPath, array $structure, $templatesPath = null)
    {
        $this->dstPath = File::symbolizePath($dstPath);
        $this->structure = $structure;

        if ($templatesPath) {
            $this->templatesPath = File::symbolizePath($templatesPath);
        }
    }

    public function setVariables($variables)
    {
        foreach ($variables as $key=>$value) {
            $this->setVariable($key, $value);
        }
    }

    public function setVariable($key, $value)
    {
        $this->variables[$key] = $value;
    }

    public function generate()
    {
        if (!File::isDirectory($this->dstPath)) {
            throw new SystemException(Lang::get('rainlab.builder::lang.common.destination_dir_not_exists', ['path'=>$this->dstPath]));
        }

        foreach ($this->structure as $key=>$value) {
            if (is_numeric($key)) {
                $this->makeDirectory($value);
            }
            else {
                $this->makeFile($key, $value);
            }
        }
    }

    protected function makeDirectory($dirPath)
    {
        $path = $this->dstPath.DIRECTORY_SEPARATOR.$dirPath;

        if (File::isDirectory($path)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_dir_exists', ['path'=>$path]));
        }

        if (!File::makeDirectory($path, 0777, true, true)) {
            throw new SystemException(Lang::get('rainlab.builder::lang.common.error_make_dir', ['name'=>$path]));
        }
    }

    protected function makeFile($filePath, $templateName)
    {
        $path = $this->dstPath.DIRECTORY_SEPARATOR.$filePath;

        if (File::isFile($path)) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_file_exists', ['path'=>$path]));
        }

        $fileDirectory = dirname($path);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new SystemException(Lang::get('rainlab.builder::lang.common.error_make_dir', ['name'=>$fileDirectory]));
            }
        }

        $templatePath = $this->templatesPath.DIRECTORY_SEPARATOR.$templateName;
        if (!File::isFile($templatePath)) {
            throw new SystemException(Lang::get('rainlab.builder::lang.common.template_not_found', ['name'=>$templateName]));
        }

        $fileContents = File::get($templatePath);
        if (!$fileContents) {
            throw new SystemException(Lang::get('rainlab.builder::lang.common.error_loading_template', ['name'=>$templateName]));
        }

        $fileContents = TextParser::parse($fileContents, $this->variables);
        if (@File::put($path, $fileContents) === false) {
            throw new SystemException(Lang::get('rainlab.builder::lang.common.error_generating_file', ['path'=>$path]));
        }

        @File::chmod($path);
    }
}