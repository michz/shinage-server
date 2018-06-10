<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UserType extends Type
{
    const ENUM_USER_TYPE    = 'enumusertype';
    const USER_TYPE_USER    = 'user';
    const USER_TYPE_ORGA    = 'organization';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('" . self::USER_TYPE_USER . "', '" . self::USER_TYPE_ORGA . "')";
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
        if (!in_array($value, [self::USER_TYPE_USER, self::USER_TYPE_ORGA])) {
            throw new \InvalidArgumentException('Invalid user type');
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::ENUM_USER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
