<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use File;
use Tailor\Classes\BlueprintIndexer;
use ApplicationException;
use Editor\VueComponents\Application;

/**
 * HasMigrations
 */
trait HasMigrations
{
    /**
     * generateContentTable
     */
    protected function generateContentTable()
    {
        if (!isset($this->activeConfig['tableName'])) {
            throw new ApplicationException('Missing a table name');
        }

        $tableName = $this->activeConfig['tableName'];

        $proposedFile = "create_{$tableName}_table.php";
        $migrationFilePath = $this->sourceModel->getPluginFilePath('updates/'.$proposedFile);

        // Find an available file name
        $counter = 2;
        while (File::isFile($migrationFilePath)) {
            $proposedFile = "create_{$tableName}_table_{$counter}.php";
            $migrationFilePath = $this->sourceModel->getPluginFilePath('updates/'.$proposedFile);
            $counter++;
        }

        // Prepare the schema from the fieldset
        $table = $this->makeSchemaBlueprint($tableName, $this->activeBlueprint);

        // Write migration to disk
        $migrationCode = '';
        foreach ($table->getColumns() as $column) {
            $migrationCode .= '\t\t\t'.$this->makeTableDefinition($column).PHP_EOL;
        }

        $code = $this->parseTemplate($this->getTemplatePath('migration.php.tpl'), [
            'migrationCode' => $this->makeTabs(trim($migrationCode, PHP_EOL))
        ]);

        $this->writeFile($migrationFilePath, $code);
    }

    /**
     * makeTableDefinition
     */
    protected function makeTableDefinition($column)
    {
        $defaultStrLength = \Illuminate\Database\Schema\Builder::$defaultStringLength;

        $code = '$table->'.$column['type'].'(\''.$column['name'].'\')';

        foreach ($column->getAttributes() as $attribute => $value) {
            if (in_array($attribute, ['name', 'type'])) {
                continue;
            }

            if ($attribute === 'length' && $value === $defaultStrLength) {
                continue;
            }

            $exportValue = $value !== true ? var_export($value, true) : '';
            $code .= '->'.$attribute.'('.$exportValue.')';
        }

        $code .= ';';

        return $code;
    }

    /**
     * makeSchemaBlueprint
     */
    protected function makeSchemaBlueprint($tableName, $blueprint)
    {
        $uuid = $blueprint->uuid;
        $fieldset = BlueprintIndexer::instance()->findContentFieldset($uuid);
        if (!$fieldset) {
            throw new ApplicationException("Unable to find content fieldset definition with UUID of '{$uuid}'.");
        }

        $table = App::make(\October\Rain\Database\Schema\Blueprint::class, ['table' => $tableName]);

        foreach ($fieldset->getAllFields() as $fieldObj) {
            $fieldObj->extendDatabaseTable($table);
        }

        return $table;
    }
}
