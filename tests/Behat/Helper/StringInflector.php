<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace Tests\Behat\Helper;

/*
 * This file is inspired by Sylius. Thanks for fantastic open source software!
 * See: https://github.com/Sylius/Sylius/blob/master/src/Sylius/Behat/Service/SharedStorage.php
 */
class StringInflector
{
    public static function nameToCode(string $value): string
    {
        return \str_replace([' ', '-'], '_', $value);
    }

    public static function nameToLowercaseCode(string $value): string
    {
        return \strtolower(self::nameToCode($value));
    }

    public static function nameToUppercaseCode(string $value): string
    {
        return \strtoupper(self::nameToCode($value));
    }
}
