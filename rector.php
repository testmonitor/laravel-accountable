<?php

use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        codeQuality: true, codingStyle: true, deadCode: true,
        phpunitCodeQuality: true,
    )
    ->withImportNames()
    ->withSkip([
        // Code quality set
        SafeDeclareStrictTypesRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        SimplifyIfReturnBoolRector::class,

        // Coding style set
        CatchExceptionNameMatchingTypeRector::class,
        SeparateMultiUseImportsRector::class,

        // PHPUnit code quality set
        DeclareStrictTypesTestsRector::class,

        // Post rectors
        NameImportingPostRector::class => [
            __DIR__ . '/config',
        ],
    ]);
