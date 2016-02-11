<?php namespace RainLab\Builder\Classes;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Enum column type.
 *
 * Currently this class is used as a placeholder. Enum columns
 * are not supported by the Builder table management UI.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class EnumDbType extends Type
{
    const TYPENAME = 'EnumDbType'; 

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // return the SQL used to create your column type. To create a portable column type, use the $platform.
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is read from the database. Make your conversions here, optionally using the $platform.
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is written to the database. Make your conversions here, optionally using the $platform.
    }

    public function getName()
    {
        return self::TYPENAME;
    }
}