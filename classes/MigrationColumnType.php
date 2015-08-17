<?php namespace RainLab\Builder\Classes;

use SystemException;
use ApplicationException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type as DoctrineType;
use Lang;

/**
 * Represents a database column type used in migrations.
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
    const TYPE_TINYINTEGER = 'tinyInteger';
    const TYPE_SMALLINTEGER = 'smallInteger';
    const TYPE_MEDIUMINTEGER = 'mediumInteger';
    const TYPE_BIGINTEGER = 'bigInteger';
    const TYPE_DATE = 'date';
    const TYPE_TIME = 'time';
    const TYPE_DATETIME = 'dateTime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_CHAR = 'char';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_MEDIUMTEXT = 'mediumText';
    const TYPE_LONGTEXT = 'longText';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DOUBLE = 'double';
    const TYPE_FLOAT = 'float';

    const REGEX_LENGTH_SINGLE = '/^([0-9]+)$/';
    const REGEX_LENGTH_DOUBLE = '/^([0-9]+)\,([0-9]+)$/';

    public static function getIntegerTypes()
    {
        return [
            self::TYPE_INTEGER,
            self::TYPE_TINYINTEGER,
            self::TYPE_SMALLINTEGER,
            self::TYPE_MEDIUMINTEGER,
            self::TYPE_MEDIUMINTEGER,
            self::TYPE_BIGINTEGER
        ];
    }

    public static function getDecimalTypes()
    {
        return [
            self::TYPE_DECIMAL,
            self::TYPE_DOUBLE,
            self::TYPE_FLOAT
        ];
    }

    /**
     * Converts a migration column type to a corresponding Doctrine mapping type name.
     */
    public static function toDoctrineTypeName($type)
    {
        $typeMap = [
            self::TYPE_INTEGER => DoctrineType::INTEGER,
            self::TYPE_TINYINTEGER => DoctrineType::BOOLEAN,
            self::TYPE_SMALLINTEGER => DoctrineType::SMALLINT,
            self::TYPE_MEDIUMINTEGER => DoctrineType::INTEGER,
            self::TYPE_BIGINTEGER => DoctrineType::BIGINT,
            self::TYPE_DATE => DoctrineType::DATE,
            self::TYPE_TIME => DoctrineType::TIME,
            self::TYPE_DATETIME => DoctrineType::DATETIME,
            self::TYPE_TIMESTAMP => DoctrineType::DATETIME,
            self::TYPE_CHAR => DoctrineType::STRING,
            self::TYPE_STRING => DoctrineType::STRING,
            self::TYPE_TEXT => DoctrineType::TEXT,
            self::TYPE_MEDIUMTEXT => DoctrineType::TEXT,
            self::TYPE_LONGTEXT => DoctrineType::TEXT,
            self::TYPE_BINARY => DoctrineType::BLOB,
            self::TYPE_BOOLEAN => DoctrineType::BOOLEAN,
            self::TYPE_DECIMAL => DoctrineType::DECIMAL,
            self::TYPE_DOUBLE => DoctrineType::FLOAT,
            self::TYPE_FLOAT => DoctrineType::FLOAT
        ];

        if (!array_key_exists($type, $typeMap)) {
            throw new SystemException(sprintf('Unknown column type: %s', $type));
        }

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
}