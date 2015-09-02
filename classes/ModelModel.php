<?php namespace RainLab\Builder\Classes;

use DirectoryIterator;
use ApplicationException;
use ValidationException;
use SystemException;
use Exception;
use Validator;
use Lang;
use File;
use Schema;
use Str;
use Db;

/**
 * Manages plugin models.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class ModelModel extends BaseModel
{
    protected static $fillable = [
        'name',
        'databaseTable'
    ];

    protected $validationRules = [
        'name' => ['required', 'regex:/^[a-z]+[a-zA-Z0-9_]+$/'],
        'databaseTable' => ['required']
    ];

    public static function listPluginModels($pluginCodeObj)
    {
        $modelsDirectoryPath = $pluginCodeObj->toPluginDirectoryPath().'/models';

        $modelsDirectoryPath = File::symbolizePath($modelsDirectoryPath);
        if (!File::isDirectory($modelsDirectoryPath)) {
            return [];
        }

        $parser = new ModelFileParser();
        foreach (new DirectoryIterator($modelsDirectoryPath) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            if ($fileInfo->getExtension() != 'php') {
                continue;
            }

            $filePath = $fileInfo->getPathname();
            $contents = File::get($filePath);

            $modelInfo = $parser->extractModelInfoFromSource($contents);
traceLog($modelInfo);
        }
    }

}