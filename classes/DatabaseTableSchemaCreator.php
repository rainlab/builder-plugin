<?php namespace RainLab\Builder\Classes;

use Doctrine\DBAL\Schema\Table;

/**
 * Creates Doctrine table schema basing on the column information array.
 *
 * The class is used by DatabaseTableModel class.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class DatabaseTableSchemaCreator extends BaseModel
{
    /**
     * @param string $name Specifies the table name.
     * @param array $columns A list of the table columns.
     * @return \Doctrine\DBAL\Schema\Table Returns the table schema.
     */
    public function createTableSchema($name, $columns)
    {
        $schema = new Table($name);

        $primaryKeyColumns = [];
        foreach ($columns as $column) {
            $type = trim($column['type']);

            $typeName = MigrationColumnType::toDoctrineTypeName($type);
            $options = $this->formatOptions($type, $column);

            $schema->addColumn($column['name'], $typeName, $options);
            if ($column['primary_key']) {
                $primaryKeyColumns[] = $column['name'];
            }
        }

        if ($primaryKeyColumns) {
            $schema->setPrimaryKey($primaryKeyColumns);
        }

        return $schema;
    }

    /**
     * Converts column options to a format supported by Doctrine\DBAL\Schema\Column
     */
    protected function formatOptions($type, $options)
    {
        $result = MigrationColumnType::lengthToPrecisionAndScale($type, $options['length']);

        $result['unsigned'] = !!$options['unsigned'];
        $result['notnull'] = !$options['allow_null'];
        $result['autoincrement'] = !!$options['auto_increment'];
        $result['comment'] = trim($options['comment'] ?? '');

        // Note - this code doesn't allow to set empty string as default.
        // But converting empty strings to NULLs is required for the further
        // work with Doctrine types. As an option - empty strings could be specified
        // as '' in the editor UI (table column editor).
        $default = trim($options['default']);
        $result['default'] = $default === '' ? null : $default;

        return $result;
    }
}
