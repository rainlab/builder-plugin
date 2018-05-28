<?php namespace RainLab\Builder\Classes;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Enum column type.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class EnumDbType extends Type
{
    const TYPENAME = 'EnumDbType';

    protected $values = [];

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $values = array_map(function($val) { return "'".$val."'"; }, $fieldDeclaration['columnDefinition']);
        return "ENUM(".implode(", ", $values).")";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->values)) {
            throw new \InvalidArgumentException("Invalid '".$this->name."' value.");
        }
        return $value;
    }

    public function getName()
    {
        return self::TYPENAME;
    }
}