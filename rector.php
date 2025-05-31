<?php

declare(strict_types = 1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap/app.php',
        __DIR__ . '/database',
        __DIR__ . '/tests',
        __DIR__ . '/routes',
        __DIR__ . '/Modules/Dynamicreports/app',
        __DIR__ . '/Modules/Dynamicreports/database',
        __DIR__ . '/Modules/Surveys/app',
        __DIR__ . '/Modules/Surveys/database',
        __DIR__ . '/Modules/InternalChat/app',
        __DIR__ . '/Modules/InternalChat/database',
        __DIR__ . '/Modules/Themes/app',
        __DIR__ . '/Modules/MailHub/app',
        __DIR__ . '/Modules/MailHub/database',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: true
    )
    ->withSkip([
        AddOverrideAttributeToOverriddenMethodsRector::class,
    ])
    ->withPhpSets();
