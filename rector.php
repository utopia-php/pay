<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Attribute\ExplicitAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\If_\ExplicitNullableObjectInstanceRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        namedArgs: true,
        instanceOf: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
        phpunitMockToStub: true,
    )
    ->withComposerBased(phpunit: true)
    ->withSkip([
        // PHPUnit attributes explicitly disallow named arguments.
        ExplicitAttributeNamedArgsRector::class,
        // Native nullable types already guarantee the alternatives to null.
        ExplicitNullableObjectInstanceRector::class,
        // Pint owns formatting; avoid inserting blank lines between every guard.
        NewlineAfterStatementRector::class,
    ]);
