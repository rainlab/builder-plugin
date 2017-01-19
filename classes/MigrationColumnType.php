<?php namespace RainLab\Builder\Classes;

use SystemException;
use ApplicationException;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Lang;

/**
 * Represents a database column type used in migrations.
 *
 * Important: some Doctrine types map to multiple migration types, for example -
 * Doctrine boolean could be boolean and tinyInteger in migrations.
 * To eliminate the the necessity of guessing, the following migration column
 * types are removed from the list:
 * 
 *  - tinyInteger
 *  - mediumInteger
 *  - char
 *  - mediumText
 *  - longText
 *  - float
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class MigrationColumnType extends BaseModel
{
    /*
     * The type names correspond to method names used in migrations.
     */

    const TYPE_INTEGER = 'integer';
    const TYPE_SMALLINTEGER = 'smallInteger';
    const TYPE_BIGINTEGER = 'bigInteger';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'dateTime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DOUBLE = 'double';

    const REGEX_LENGTH_SINGLE = '/^([0-9]+)$/';
    const REGEX_LENGTH_DOUBLE = '/^([0-9]+)\,([0-9]+)$/';

    public static function getIntegerTypes()
    {
        return [
            self::TYPE_INTEGER,
            self::TYPE_SMALLINTEGER,
            self::TYPE_BIGINTEGER
        ];
    }

    public static function getDecimalTypes()
    {
        return [
            self::TYPE_DECIMAL,
            self::TYPE_DOUBLE
        ];
    }

    public static function getDoctrineTypeMap()
    {
        return [
            self::TYPE_INTEGER => DoctrineType::INTEGER,
            self::TYPE_SMALLINTEGER => DoctrineType::SMALLINT,
            self::TYPE_BIGINTEGER => DoctrineType::BIGINT,
            self::TYPE_DATE => DoctrineType::DATE,
            self::TYPE_TIME => DoctrineType::TIME,
            self::TYPE_DATETIME => DoctrineType::DATETIME,
            self::TYPE_TIMESTAMP => DoctrineType::DATETIME,
            self::TYPE_STRING => DoctrineType::STRING,
            self::TYPE_TEXT => DoctrineType::TEXT,
            self::TYPE_BINARY => DoctrineType::BLOB,
            self::TYPE_BOOLEAN => DoctrineType::BOOLEAN,
            self::TYPE_DECIMAL => DoctrineType::DECIMAL,
            self::TYPE_DOUBLE => DoctrineType::FLOAT
        ];
    }

    /**
     * Converts a migration column type to a corresponding Doctrine mapping type name.
     */
    public static function toDoctrineTypeName($type)
    {
        $typeMap = self::getDoctrineTypeMap();

        if (!array_key_exists($type, $typeMap)) {
            throw new SystemException(sprintf('Unknown column type: %s', $type));
        }

        return $typeMap[$type];
    }

    /**
     * Converts Doctrine mapping type name to a migration column method name
     */
    public static function toMigrationMethodName($type, $columnName)
    {
        $typeMap = self::getDoctrineTypeMap();

        if (!in_array($type, $typeMap)) {
            throw new SystemException(sprintf('Unknown column type: %s', $type));
        }

        // Some Doctrine types map to multiple migration types, for example
        // Doctrine boolean could be boolean and tinyInteger in migrations.
        // Some guessing could be required in this method. The method is not
        // 100% reliable.

        if ($type == DoctrineType::DATETIME) {
            // The datetime type maps to datetime and timestamp. Use the name 
            // guessing as the only possible solution.

            if (in_array($columnName, ['created_at', 'updated_at', 'deleted_at', 'published_at', 'deleted_at'])) {
                return self::TYPE_TIMESTAMP;
            }

            return self::TYPE_DATETIME;
        }

        $typeMap = array_flip($typeMap);
        return $typeMap[$type];
    }

    /**
     * Validates the column length parameter basing on the column type
     */
    public static function validateLength($type, $value)
    {
        $value = trim($value);

        if (!strlen($value)) {
            return;
        }

        if (in_array($type, self::getDecimalTypes())) {
            if (!preg_match(self::REGEX_LENGTH_DOUBLE, $value)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.database.error_table_decimal_length', [
                    'type' => $type
                ]));
            }
        } else {
            if (!preg_match(self::REGEX_LENGTH_SINGLE, $value)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.database.error_table_length', [
                    'type' => $type
                ]));
            }
        }
    }

    /**
     * Returns an array containing a column length, precision and scale, basing on the column type.
     */
    public static function lengthToPrecisionAndScale($type, $length)
    {
        $length = trim($length);

        if (!strlen($length)) {
            return [];
        }

        $result = [
            'length' => null,
            'precision' => null,
            'scale' => null
        ];

        if (in_array($type, self::getDecimalTypes())) {
            $matches = [];

            if (!preg_match(self::REGEX_LENGTH_DOUBLE, $length, $matches)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.database.error_table_length', [
                    'type' => $type
                ]));
            }

            $result['precision'] = $matches[1];
            $result['scale'] = $matches[2];

            return $result;
        }

        if (in_array($type, self::getIntegerTypes())) {
            if (!preg_match(self::REGEX_LENGTH_SINGLE, $length)) {
                throw new ApplicationException(Lang::get('rainlab.builder::lang.database.error_table_length', [
                    'type' => $type
                ]));
            }

            $result['precision'] = $length;
            $result['scale'] = 0;

            return $result;
        }

        $result['length'] = $length;
        return $result;
    }

    /**
     * Converts Doctrine length, precision and scale to migration-compatible length string
     * @return string
     */
    public static function doctrineLengthToMigrationLength($column)
    {
        $typeName = $column->getType()->getName();
        $migrationTypeName = self::toMigrationMethodName($typeName, $column->getName());

        if (in_array($migrationTypeName, self::getDecimalTypes())) {
            return $column->getPrecision().','.$column->getScale();
        }

        if (in_array($migrationTypeName, self::getIntegerTypes())) {
            return $column->getPrecision();
        }

        return $column->getLength();
    }
}
