<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 16:46
 */

namespace AppBundle;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class UserType extends Type
{

    const ENUM_USER_TYPE    = 'enumusertype';
    const USER_TYPE_USER    = 'user';
    const USER_TYPE_ORGA    = 'organization';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('".self::USER_TYPE_USER."', '".self::USER_TYPE_ORGA."')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(self::USER_TYPE_USER, self::USER_TYPE_ORGA))) {
            throw new \InvalidArgumentException("Invalid user type");
        }
        return $value;
    }

    public function getName()
    {
        return self::ENUM_USER_TYPE;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
