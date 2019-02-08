<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

if (class_exists(Dotenv::class)) {
    (new Dotenv())->load(__DIR__ . '/../../.env');
}
