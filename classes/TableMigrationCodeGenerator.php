<?php namespace RainLab\Builder\Classes;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\TableDiff;
use October\Rain\Parse\Template as TextParser;
use File;
use Str;

/**
 * Generates migration code for creating, updates and deleting tables.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class TableMigrationCodeGenerator extends BaseModel
{
    const COLUMN_MODE_CREATE = 'create';
    const COLUMN_MODE_CHANGE = 'change';
    const COLUMN_MODE_REVERT = 'revert';

    protected $indent = '    ';

    protected $eol = PHP_EOL;

    /**
     * Generates code for creating or updating a database table.
     * @param Doctrine\DBAL\Schema\Table $updatedTable Specifies the updated table schema.
     * @param Doctrine\DBAL\Schema\Table $existingTable Specifies the existing table schema, if applicable.
     * @param string $newTableName An updated name of the theme. 
     * @return string|boolean Returns the migration up() and down() methods code. 
     * Returns false if there the table was not changed.
     */
    public function createOrUpdateTable($updatedTable, $existingTable, $newTableName)
    {
        $tableDiff = false;

        if ($existingTable !== null) {
            // The table already exists
            //
            $comparator = new Comparator();
            $tableDiff = $comparator->diffTable($existingTable, $updatedTable);

            if ($newTableName !== $existingTable->getName()) {
                if (!$tableDiff) {
                    $tableDiff = new TableDiff($existingTable->getName());
                }

                $tableDiff->newName = $newTableName;
            }
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

        if (!$this->tableHasChanges($tableDiff)) {
            return false;
        }

        return $this->generateCreateOrUpdateCode($tableDiff, !$existingTable);
    }

    /**
     * Wrap migration's up() and down() functions into a complete migration class declaration
     * @param string $scriptFileName Specifies the migration script file name
     * @param string $code Specifies the migration code
     * @param PluginCode $pluginCodeObj The plugin code object
     */
    public function wrapMigrationCode($scriptFilename, $code, $pluginCodeObj)
    {
        $templatePath = '$/rainlab/builder/classes/databasetablemodel/templates/full-migration-code.php.tpl';
        $templatePath = File::symbolizePath($templatePath);

        $fileContents = File::get($templatePath);

        return TextParser::parse($fileContents, [
            'className' => Str::studly($scriptFilename),
            'migrationCode' => $this->indent($code),
            'namespace' => $pluginCodeObj->toPluginNamespace()
        ]);
    }

    protected function generateCreateOrUpdateCode($tableDiff, $isNewTable)
    {
        $templatePath = '$/rainlab/builder/classes/databasetablemodel/templates/migration-code.php.tpl';
        $templatePath = File::symbolizePath($templatePath);

        $fileContents = File::get($templatePath);

        return TextParser::parse($fileContents, [
            'upCode' => $this->generateCreateOrUpdateUpCode($tableDiff, $isNewTable),
            'downCode' => $this->generateCreateOrUpdateDownCode($tableDiff, $isNewTable),
        ]);
    }

    protected function generateCreateOrUpdateUpCode($tableDiff, $isNewTable)
    {
        $result = null;

        $hasColumnChanges = $this->tableHasChanges($tableDiff, true);

        if ($tableDiff->getNewName()) {
            $result .= $this->generateTableRenameCode($tableDiff->name, $tableDiff->newName);

            if ($hasColumnChanges) {
                $result .= $this->eol;
            }
        }

        if (!$hasColumnChanges) {
            return $this->makeTabs($result);
        }

        $tableName = $tableDiff->getNewName() ? $tableDiff->newName : $tableDiff->name;
        $result .= $this->generateSchemaTableMethodStart($tableName, $isNewTable);

        foreach ($tableDiff->addedColumns as $column) {
            $result .= $this->generateColumnCode($column, self::COLUMN_MODE_CREATE);
        }

        foreach ($tableDiff->changedColumns as $columnDiff) {
            $result .= $this->generateColumnCode($columnDiff, self::COLUMN_MODE_CHANGE);
        }

        foreach ($tableDiff->renamedColumns as $oldName=>$column) {
            $result .= $this->generateColumnRenameCode($oldName, $column->getName());
        }

        foreach ($tableDiff->removedColumns as $name=>$column) {
            $result .= $this->generateColumnRemoveCode($name);
        }

        foreach ($tableDiff->addedIndexes as $index) {
            if (!$index->isPrimary()) {
                // TODO: implement indexes
            }
        }

        $result .= $this->generateSchemaTableMethodEnd();

        return $this->makeTabs($result);

    }

    protected function generateCreateOrUpdateDownCode($tableDiff, $isNewTable)
    {
        $result = '';

        if ($isNewTable) {
            $result = sprintf('\tSchema::dropIfExists(\'%s\');', $tableDiff->name);
        }
        else {
            if ($this->tableHasChanges($tableDiff)) {
                $hasColumnChanges = $this->tableHasChanges($tableDiff, true);

                if ($tableDiff->getNewName()) {
                    $result .= $this->generateTableRenameCode($tableDiff->newName, $tableDiff->name);

                    if ($hasColumnChanges) {
                        $result .= $this->eol;
                    }
                }

                if (!$hasColumnChanges) {
                    return $this->makeTabs($result);
                }

                $result .= $this->generateSchemaTableMethodStart($tableDiff->name, $isNewTable);

                foreach ($tableDiff->addedColumns as $column) {
                    $result .= $this->generateColumnDrop($column);
                }

                foreach ($tableDiff->changedColumns as $columnDiff) {
                    $result .= $this->generateColumnCode($columnDiff, self::COLUMN_MODE_REVERT);
                }

                foreach ($tableDiff->renamedColumns as $oldName=>$column) {
                    $result .= $this->generateColumnRenameCode($column->getName(), $oldName);
                }

                foreach ($tableDiff->removedColumns as $name=>$column) {
                    $result .= $this->generateColumnCode($column, self::COLUMN_MODE_CREATE);
                }

                $result .= $this->generateSchemaTableMethodEnd();
            }
        }

        return $this->makeTabs($result);
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

    protected function applyMethodIncrements($method, $column)
    {
        if (!$column->getAutoincrement()) {
            return $method;
        }

        if ($method == MigrationColumnType::TYPE_BIGINTEGER) {
            return 'bigIncrements';
        }

        return 'increments';
    }

    protected function generateSchemaTableMethodStart($tableName, $isNewTable)
    {
        $tableFunction = $isNewTable ? 'create' : 'table';
        $result = sprintf('\tSchema::%s(\'%s\', function($table)', $tableFunction, $tableName).$this->eol;
        $result .= '\t{'.$this->eol;

        if ($isNewTable) {
            $result .= '\t\t$table->engine = \'InnoDB\';'.$this->eol;
        }

        return $result;
    }

    protected function generateSchemaTableMethodEnd()
    {
        return '\t});';
    }

    protected function generateColumnDrop($column)
    {
        return sprintf('\t\t$table->dropColumn(\'%s\');', $column->getName()).$this->eol;
    }

    protected function generateColumnCode($columnData, $mode)
    {
        $forceFlagsChange = false;

        switch ($mode) {
            case self::COLUMN_MODE_CREATE: 
                $column = $columnData;
                $changeMode = false;
            break;
            case self::COLUMN_MODE_CHANGE: 
                $column = $columnData->column;
                $changeMode = true;

                $forceFlagsChange = in_array('type', $columnData->changedProperties);
            break;
            case self::COLUMN_MODE_REVERT: 
                $column = $columnData->fromColumn;
                $changeMode = true;

                $forceFlagsChange = in_array('type', $columnData->changedProperties);
            break;
        }

        $result = $this->generateColumnMethodCall($column);
        $result .= $this->generateNullable($column, $changeMode, $columnData, $forceFlagsChange);
        $result .= $this->generateUnsigned($column, $changeMode, $columnData, $forceFlagsChange);
        $result .= $this->generateDefault($column, $changeMode, $columnData, $forceFlagsChange);
 
        if ($changeMode) {
            $result .= '->change()';
        }

        $result .= ';'.$this->eol;

        return $result;
    }

    protected function generateColumnRenameCode($fromName, $toName)
    {
        return sprintf('\t\t$table->renameColumn(\'%s\', \'%s\');', $fromName, $toName).$this->eol;
    }

    protected function generateTableRenameCode($fromName, $toName)
    {
        return sprintf('\tSchema::rename(\'%s\', \'%s\');', $fromName, $toName);
    }

    protected function generateColumnRemoveCode($name)
    {
        return sprintf('\t\t$table->dropColumn(\'%s\');', $name).$this->eol;
    }

    protected function generateColumnMethodCall($column)
    {
        $columnName = $column->getName();
        $typeName = $column->getType()->getName();

        $method = MigrationColumnType::toMigrationMethodName($typeName, $columnName);
        $method = $this->applyMethodIncrements($method, $column);

        $lengthStr = $this->formatLengthParameters($column, $method);
        return sprintf('\t\t$table->%s(\'%s\'%s)', $method, $columnName, $lengthStr);
    }

    protected function generateNullable($column, $changeMode, $columnData, $forceFlagsChange)
    {
        $result = null;

        if (!$changeMode) {
            if (!$column->getNotnull()) {
                $result = $this->generateBooleanMethod('nullable', true);
            }
        }
        elseif (in_array('notnull', $columnData->changedProperties) || $forceFlagsChange) {
            $result = $this->generateBooleanMethod('nullable', !$column->getNotnull());
        }

        return $result;
    }

    protected function generateUnsigned($column, $changeMode, $columnData, $forceFlagsChange)
    {
        $result = null;

        if (!$changeMode) {
            if ($column->getUnsigned()) {
                $result = $this->generateBooleanMethod('unsigned', true);
            }
        }
        elseif (in_array('unsigned', $columnData->changedProperties) || $forceFlagsChange) {
            $result = $this->generateBooleanMethod('unsigned', $column->getUnsigned());
        }

        return $result;
    }

    protected function generateDefault($column, $changeMode, $columnData, $forceFlagsChange)
    {
        // See a note about empty strings as default values in
        // DatabaseTableSchemaCreator::formatOptions() method.

        $result = null;
        $default = $column->getDefault();

        if (!$changeMode) {
            if (strlen($default)) {
                $result = $this->generateDefaultMethodCall($default, $column);
            }
        }
        elseif (in_array('default', $columnData->changedProperties) || $forceFlagsChange) {
            if (strlen($default)) {
                $result = $this->generateDefaultMethodCall($default, $column);
            }
            elseif ($changeMode) {
                $result = sprintf('->default(null)');
            }
        }

        return $result;
    }

    protected function generateDefaultMethodCall($default, $column)
    {
        $columnName = $column->getName();
        $typeName = $column->getType()->getName();

        $type = MigrationColumnType::toMigrationMethodName($typeName, $columnName);

        if (in_array($type, MigrationColumnType::getIntegerTypes()) || 
            in_array($type, MigrationColumnType::getDecimalTypes()) ||
            $type == MigrationColumnType::TYPE_BOOLEAN) {
            return sprintf('->default(%s)', $default);
        }

        return sprintf('->default(\'%s\')', $this->quoteParameter($default));
    }

    protected function generateBooleanString($value)
    {
        $result = $value ? 'true' : 'false';

        return$result;
    }

    protected function generateBooleanMethod($methodName, $value)
    {
        if ($value) {
            return '->'.$methodName.'()';
        }

        return '->'.$methodName.'('.$this->generateBooleanString($value).')';
    }

    protected function quoteParameter($str)
    {
        return str_replace("'", "\'", $str);
    }

    protected function makeTabs($str)
    {
        return str_replace('\t', '    ', $str);
    }

    protected function indent($str)
    {
        return $this->indent . str_replace($this->eol, $this->eol . $this->indent, $str);
    }

    private function tableHasChanges($tableDiff, $columnChangesOnly = false)
    {
        $result = $tableDiff->addedColumns 
                || $tableDiff->changedColumns 
                || $tableDiff->removedColumns 
                || $tableDiff->renamedColumns;

        if ($columnChangesOnly) {
            return $result;
        }

        return $result || $tableDiff->getNewName();
    }
}