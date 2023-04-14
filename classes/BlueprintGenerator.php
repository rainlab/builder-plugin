<?php namespace RainLab\Builder\Classes;

use Lang;
use File;
use Twig;
use RainLab\Builder\Classes\TailorBlueprintLibrary;
use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;

/**
 * BlueprintGenerator is a helper class for generating controller class files and associated files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class BlueprintGenerator
{
    /**
     * @var object sourceModel
     */
    protected $sourceModel;

    /**
     * @var array templateVars
     */
    protected $templateVars;

    /**
     * @var array filesGenerated
     */
    protected $filesGenerated;

    /**
     * @var object activeBlueprint
     */
    protected $activeBlueprint;

    /**
     * @var array activeConfig
     */
    protected $activeConfig;

    /**
     * __construct
     */
    public function __construct($source)
    {
        $this->sourceModel = $source;
    }

    /**
     * generate
     */
    public function generate()
    {
        $this->filesGenerated = [];
        $this->templateVars = [];

        try {
            foreach ($this->sourceModel->blueprints as $uuid => $config) {
                $blueprintLib = TailorBlueprintLibrary::instance();
                $blueprint = $blueprintLib->getBlueprintObject($uuid);
                if ($blueprint) {
                    $this->generateBlueprint($blueprint, $config);
                }
            }
        }
        catch (Exception $ex) {
            $this->rollback();
            throw $ex;
        }
    }

    /**
     * generateBlueprint
     */
    protected function generateBlueprint($blueprint, $config)
    {
        $this->activeBlueprint = $blueprint;
        $this->activeConfig = $config;

        $this->setTemplateVars();
        $this->generateMigration();
    }

    /**
     * setTemplateVars
     */
    protected function setTemplateVars()
    {
        $pluginCodeObj = $this->sourceModel->getPluginCodeObj();

        $this->templateVars['pluginNamespace'] = $pluginCodeObj->toPluginNamespace();
        $this->templateVars['pluginCode'] = $pluginCodeObj->toCode();
    }

    /**
     * getTemplatePath
     */
    protected function getTemplatePath($template)
    {
        return __DIR__.'/blueprintgenerator/templates/'.$template;
    }

    /**
     * parseTemplate
     */
    protected function parseTemplate($templatePath, $vars = [])
    {
        $template = File::get($templatePath);

        $vars = array_merge($this->templateVars, $vars);
        $code = Twig::parse($template, $vars);

        return $code;
    }

    /**
     * writeFile
     */
    protected function writeFile($path, $data)
    {
        $fileDirectory = dirname($path);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_make_dir', [
                    'name' => $fileDirectory
                ]));
            }
        }

        if (@File::put($path, $data) === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.controller.error_save_file', [
                'file' => basename($path)
            ]));
        }

        @File::chmod($path);
        $this->filesGenerated[] = $path;
    }

    /**
     * rollback
     */
    protected function rollback()
    {
        foreach ($this->filesGenerated as $path) {
            @unlink($path);
        }
    }

    /**
     * generateMigration for a blueprint, returns the migration file name
     */
    protected function generateMigration(): string
    {
        $tableName = $this->activeConfig['tableName'] ?? 'unknown';

        $proposedFile = "create_{$tableName}_table.php";
        $migrationFilePath = $this->sourceModel->getPluginFilePath('updates/'.$proposedFile);

        $counter = 2;
        while (File::isFile($migrationFilePath)) {
            $proposedFile = "create_{$tableName}_table_{$counter}.php";
            $migrationFilePath = $this->sourceModel->getPluginFilePath('updates/'.$proposedFile);
            $counter++;
        }

        $this->writeFile($migrationFilePath, '<?php echo "test";');

        return $proposedFile;
    }
}
