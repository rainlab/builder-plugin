<?php namespace RainLab\Builder\Classes\BlueprintGenerator;

use App;
use File;
use ApplicationException;

/**
 * HasMigrations
 */
trait HasMigrations
{
    /**
     * generateMigration for a blueprint, returns the migration file name
     */
    protected function generateMigration()
    {
        $this->generateContentTable();
    }

    /**
     * generateContentTable
     */
    protected function generateContentTable()
    {
        $tableName = $this->getConfig('tableName');
        if (!$tableName) {
            throw new ApplicationException('Missing a table name');
        }

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
        $table = $this->makeSchemaBlueprint($tableName);

        // Write migration to disk
        $migrationCode = '';
        foreach ($table->getColumns() as $column) {
            $migrationCode .= '\t\t\t'.$this->makeTableDefinition($column).PHP_EOL;
        }

        $code = $this->parseTemplate($this->getTemplatePath('migration.php.tpl'), [
            'migrationCode' => $this->makeTabs(trim($migrationCode, PHP_EOL))
        ]);

        $this->writeFile($migrationFilePath, $code);

        $this->migrationScripts[] = $proposedFile;
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
    protected function makeSchemaBlueprint($tableName)
    {
        $table = App::make(\October\Rain\Database\Schema\Blueprint::class, ['table' => $tableName]);

        $fieldset = $this->sourceModel->getBlueprintFieldset();
        foreach ($fieldset->getAllFields() as $fieldObj) {
            $fieldObj->extendDatabaseTable($table);
        }

        return $table;
    }
}
