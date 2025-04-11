<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

return $config
    ->addPathToExclude(__DIR__ . '/src/DataFixtures/ORM')
    ->ignoreErrorsOnPackages(
        [
            'doctrine/migrations',
        ],
        [ErrorType::PROD_DEPENDENCY_ONLY_IN_DEV]
    )
    ->ignoreErrorsOnPackages(
        [
            'composer/package-versions-deprecated',
            'doctrine/cache',
            'doctrine/doctrine-bundle',
            'doctrine/doctrine-migrations-bundle',
            'endroid/qr-code',
            'endroid/qr-code-bundle',
            'jms/serializer-bundle',
            'knplabs/knp-menu-bundle',
            'scheb/2fa-bundle',
            'symfony/cache',
            'symfony/expression-language',
            'symfony/flex',
            'symfony/monolog-bundle',
            'symfony/proxy-manager-bridge',
            'symfony/security-bundle',
            'symfony/translation',
            'symfony/twig-bundle',
        ],
        [ErrorType::UNUSED_DEPENDENCY]
    )
    ->ignoreErrorsOnExtensions(
        [
            'ext-gd',
            'ext-intl',
        ],
        [ErrorType::UNUSED_DEPENDENCY]
    )
    ;
