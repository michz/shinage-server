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

class ScreenRoleType extends Type
{

    const ENUM_SCREENROLE   = 'enumscreenrole';
    const ROLE_ADMIN        = 'admin';
    const ROLE_MANAGER      = 'manager';
    const ROLE_AUTHOR       = 'author';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('admin', 'manager', 'author')";
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, array(self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_AUTHOR))) {
            throw new \InvalidArgumentException("Invalid screenrole");
        }
        return $value;
    }

    public function getName()
    {
        return self::ENUM_SCREENROLE;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
