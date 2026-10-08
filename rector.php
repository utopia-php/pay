<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Attribute\ExplicitAttributeNamedArgsRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;

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
        naming: true,
        namedArgs: true,
        instanceOf: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
        phpunitMockToStub: true,
    )
    ->withComposerBased(phpunit: true)
    ->withSkip([
        // Public parameter names are part of the named-argument API.
        RenameParamToMatchTypeRector::class,
        // PHPUnit attributes explicitly disallow named arguments.
        ExplicitAttributeNamedArgsRector::class,
        // Native nullable types already guarantee the alternatives to null.
        FlipTypeControlToUseExclusiveTypeRector::class,
        EmptyOnNullableObjectToInstanceOfRector::class,
        // Pint owns formatting; avoid inserting blank lines between every guard.
        NewlineAfterStatementRector::class,
    ]);
