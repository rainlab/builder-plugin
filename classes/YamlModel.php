<?php namespace RainLab\Builder\Classes;

use Symfony\Component\Yaml\Dumper as YamlDumper;
use ApplicationException;
use ValidationException;
use Exception;
use Yaml;
use File;
use Lang;

/**
 * Base class for models that store data in YAML files.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class YamlModel extends BaseModel
{
    /**
     * @var string Section in the YAML file to save the data into.
     * If empty, the model contents uses the entire file.
     */
    protected $yamlSection;

    protected $originalFilePath;

    protected $originalFileData = [];

    public function save()
    {
        $this->validate();

        if ($this->isNewModel()) {
            $this->beforeCreate();
        }

        $data = $this->modelToYamlArray();

        if ($this->yamlSection) {
            $fileData = $this->originalFileData;

            if ($data) {
                // Save the section data only if the section 
                // is not empty.
                $fileData[$this->yamlSection] = $data;
            } else {
                if (array_key_exists($this->yamlSection, $fileData)) {
                    unset($fileData[$this->yamlSection]);
                }
            }
            $data = $fileData;
        }

        $dumper = new YamlDumper();

        if ($data !== null) {
            $yamlData = $dumper->dump($data, 20, 0, false, true);
        }
        else {
            $yamlData = '';
        }

        $filePath = File::symbolizePath($this->getFilePath());
        $isNew = $this->isNewModel();

        if (File::isFile($filePath)) {
            if ($isNew || $this->originalFilePath != $filePath) {
                throw new ValidationException(['fileName' => Lang::get('rainlab.builder::lang.common.error_file_exists', ['path'=>basename($filePath)])]);
            }
        }

        $fileDirectory = dirname($filePath);
        if (!File::isDirectory($fileDirectory)) {
            if (!File::makeDirectory($fileDirectory, 0777, true, true)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.common.error_make_dir', ['name'=>$fileDirectory]));
            }
        }

        if (@File::put($filePath, $yamlData) === false) {
            throw new ApplicationException(Lang::get('rainlab.builder::lang.yaml.save_error', ['name'=>$filePath]));
        }

        @File::chmod($filePath);

        if ($this->isNewModel()) {
            $this->afterCreate();
        }

        if ($this->yamlSection) {
            $this->originalFileData = $data;
        }

        if (strlen($this->originalFilePath) > 0 && $this->originalFilePath != $filePath) {
            @File::delete($this->originalFilePath);
        }

        $this->originalFilePath = $filePath;
    }

    protected function load($filePath)
    {
        $filePath = File::symbolizePath($filePath);

        if (!File::isFile($filePath)) {
            throw new ApplicationException('Cannot load the model - the original file is not found: '.basename($filePath));
        }

        try {
            $data = Yaml::parse(File::get($filePath));
        } 
        catch (Exception $ex) {
            throw new ApplicationException(sprintf('Cannot parse the YAML file %s: %s', basename($filePath), $ex->getMessage()));
        }

        $this->originalFilePath = $filePath;

        if ($this->yamlSection) {
            $this->originalFileData = $data;
            if (!is_array($this->originalFileData)) {
                $this->originalFileData = [];
            }

            if (array_key_exists($this->yamlSection, $data)) {
                $data = $data[$this->yamlSection];
            }
            else {
                $data = [];
            }
        }

        $this->yamlArrayToModel($data);
    }

    public function deleteModel()
    {
        if (!File::isFile($this->originalFilePath)) {
            throw new ApplicationException('Cannot load the model - the original file is not found: '.$filePath);
        }

        if (strtolower(substr($this->originalFilePath, -5)) !== '.yaml') {
            throw new ApplicationException('Cannot delete the model - the original file should be a YAML document');
        }

        File::delete($this->originalFilePath);
    }

    public function initDefaults()
    {
    }

    public function isNewModel()
    {
        return !strlen($this->originalFilePath);
    }

    protected function beforeCreate()
    {
    }

    protected function afterCreate()
    {
    }

    protected function getArrayKeySafe($array, $key, $default = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Converts the model's data to an array before it's saved to a YAML file.
     * @return array
     */
    abstract protected function modelToYamlArray();

    /**
     * Load the model's data from an array.
     * @param array $array An array to load the model fields from.
     */
    abstract protected function yamlArrayToModel($array);

    /**
     * Returns a file path to save the model to.
     * @return string Returns a path.
     */
    abstract protected function getFilePath();
}