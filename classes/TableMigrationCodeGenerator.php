<?php namespace RainLab\Builder\Classes;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\TableDiff;
use October\Rain\Parse\Template as TextParser;
use File;

/**
 * Generates migration code for creating, updates and deleting tables.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class TableMigrationCodeGenerator extends BaseModel
{
    /**
     * Generates code for creating or updating a database table.
     * @param Doctrine\DBAL\Schema\Table $updatedTable Specifies the updated table schema.
     * @param Doctrine\DBAL\Schema\Table $existingTable Specifies the existing table schema, if applicable.
     * @return string|boolean Returns the migration up() and down() methods code. 
     * Returns false if there the table was not changed.
     */
    public function createOrUpdateTable($updatedTable, $existingTable = null)
    {
        $tableDiff = false;

        if ($existingTable !== null) {
            // The table already exists
            //
            $comparator = new Comparator();
            $tableDiff = $comparator->diffTable($updatedTable, $existingTable);
        }
        else {
            // The table doesn't exist
            //
            $tableDiff = new TableDiff(
                $updatedTable->getName(), 
                $updatedTable->getColumns(),
                [], // Changed columns
                [], // Removed columns
                $updatedTable->getIndexes() // Added indexes
            );
        }

        if (!$tableDiff) {
            return false;
        }

        return $this->generateCreateOrUpdateCode($tableDiff, !$existingTable);
    }

    protected function generateCreateOrUpdateCode($tableDiff, $isNewTable)
    {

        $templatePath = '$/rainlab/builder/classes/databasetablemodel/templates/migration-code.php.tpl';
        $templatePath = File::symbolizePath($templatePath);

        $fileContents = File::get($templatePath);

        return TextParser::parse($fileContents, [
            'upCode' => $this->generateCreateOrUpdateUpCode($tableDiff),
            'downCode' => $this->generateCreateOrUpdateDownCode($tableDiff, $isNewTable),
        ]);
    }

    protected function generateCreateOrUpdateUpCode($tableDiff)
    {
        // TODO: Implement renaming, implement indexes
        // write unit tests

        $result = sprintf('\tSchema::table(\'%s\', function($table)', $tableDiff->name).PHP_EOL;
        $result .= '\t{'.PHP_EOL;

        $autoincrementColumn = null;
        foreach ($tableDiff->addedColumns as $column) {
            $columnName = $column->getName();
            $typeName = $column->getType()->getName();

            $method = MigrationColumnType::toMigrationMethodName($typeName, $columnName);

            $lengthStr = $this->formatLengthParameters($column, $method);
            $result .= sprintf('\t\t$table->%s(\'%s\'%s)', $method, $columnName, $lengthStr);

            if (!$column->getNotnull()) {
                $result .= '->nullable()';
            }

            if ($column->getUnsigned()) {
                $result .= '->unsigned()';
            }

            $default = $column->getDefault();
            if (strlen($default)) {
                $result .= sprintf('->default(\'%s\')', $this->quoteParameter($default));
            }

            $result .= ';'.PHP_EOL;

            if ($column->getAutoincrement()) {
                $autoincrementColumn = $columnName;
            }
        }

        if ($autoincrementColumn !== null) {
            $result .= sprintf('\t\t$table->increments(\'%s\');', $autoincrementColumn).PHP_EOL;
        }

        foreach ($tableDiff->addedIndexes as $index) {
            if (!$index->isPrimary()) {
                // TODO: implement indexes
            }
        }

        $result .= '\t});';

        return $this->makeTabs($result);

    }

    protected function generateCreateOrUpdateDownCode($tableDiff, $isNewTable)
    {
        $result = '';

        if ($isNewTable) {
            $result = sprintf('\tSchema::dropIfExists(\'%s\');', $tableDiff->name);
        }

        return $this->makeTabs($result);
    }

    protected function makeTabs($str)
    {
        return str_replace('\t', '    ', $str);
    }

    protected function formatLengthParameters($column, $method)
    {
        $length = $column->getLength();
        $precision = $column->getPrecision();
        $scale = $column->getScale();

        if (!strlen($length) && !strlen($precision)) {
            return null;
        }

        if ($method == MigrationColumnType::TYPE_STRING) {
            if (!strlen($length)) {
                return null;
            }

            return ', '.$length;
        }

        if ($method == MigrationColumnType::TYPE_DECIMAL || $method == MigrationColumnType::TYPE_DOUBLE) {
            if (!strlen($precision)) {
                return null;
            }

            if (strlen($scale)) {
                return ', '.$precision.', '.$scale;
            }

            return ', '.$precision;
        }
    }

    protected function quoteParameter($str)
    {
        return str_replace("'", "\'", $str);
    }
}