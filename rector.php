<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withRules([
        AddFunctionVoidReturnTypeWhereNoReturnRector::class,
    ])
    ->withSets([SetList::PHP_84])
    ;

/*
return function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        ReturnTypeFromStrictNativeCallRector::class,
        #DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        #SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        #NetteSetList::ANNOTATIONS_TO_ATTRIBUTES,
        #SensiolabsSetList::FRAMEWORK_EXTRA_61,
    ]);
};
*/
