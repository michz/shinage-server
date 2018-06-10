<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ScreenRoleType extends Type
{
    const ENUM_SCREENROLE   = 'enumscreenrole';
    const ROLE_ADMIN        = 'admin';
    const ROLE_MANAGER      = 'manager';
    const ROLE_AUTHOR       = 'author';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('admin', 'manager', 'author')";
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_AUTHOR])) {
            throw new \InvalidArgumentException('Invalid screenrole');
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::ENUM_SCREENROLE;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
