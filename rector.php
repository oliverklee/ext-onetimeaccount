<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

/**
 * This configuration file is for Rector 0.13.4. Higher versions need the dedicated TYPO3 Rector
 * package and a different configuration.
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths(
        [
            __DIR__ . '/Classes',
            __DIR__ . '/Configuration',
            __DIR__ . '/Tests',
        ]
    );

    $rectorConfig->parameters()->set(Option::ENABLE_EDITORCONFIG, true);

    // Disable parallel processing. Otherwise, non-PHP file processing is not working (TypoScript).
    $rectorConfig->disableParallel();

    // Define your target version which you want to support.
    $rectorConfig->phpVersion(PhpVersion::PHP_72);

    // In order to have a better analysis from PHPStan, we teach it here some more things.
    $rectorConfig->phpstanConfig(Typo3Option::PHPSTAN_FOR_RECTOR_PATH);

    // auto-import names, but not classes in doc blocks
    $rectorConfig->importNames();
    // this will not import root namespace classes, like \DateTime or \Exception
    $rectorConfig->disableImportShortClasses();

    // define sets of rules
    $rectorConfig->sets([
        // LevelSetList::UP_TO_PHP_73,
        // LevelSetList::UP_TO_PHP_74,
        // LevelSetList::UP_TO_PHP_80,
        // LevelSetList::UP_TO_PHP_81,
        // LevelSetList::UP_TO_PHP_82,

        // https://github.com/sabbelasichon/typo3-rector/blob/main/src/Set/Typo3LevelSetList.php
        // https://github.com/sabbelasichon/typo3-rector/blob/main/src/Set/Typo3SetList.php

        // Typo3SetList::TYPO3_95,
        // Typo3SetList::TCA_95,
        // Typo3SetList::TYPOSCRIPT_CONDITIONS_95,
        // Typo3SetList::COMPOSER_PACKAGES_95_CORE,
        // Typo3SetList::COMPOSER_PACKAGES_95_EXTENSIONS,

        // Typo3SetList::TYPO3_104,
        // Typo3SetList::TCA_104,
        // Typo3SetList::TYPOSCRIPT_100,
        // Typo3SetList::TYPOSCRIPT_CONDITIONS_104,
        // Typo3SetList::COMPOSER_PACKAGES_104_CORE,
        // Typo3SetList::COMPOSER_PACKAGES_104_EXTENSIONS,

        // Typo3SetList::TYPO3_11,
        // Typo3SetList::TCA_110,
        // Typo3SetList::COMPOSER_PACKAGES_110_CORE,
        // Typo3SetList::COMPOSER_PACKAGES_110_EXTENSIONS,

        // Typo3SetList::TYPO3_12,
        // Typo3SetList::TCA_120,
        // Typo3SetList::TYPOSCRIPT_120,

        // Typo3SetList::DATABASE_TO_DBAL,
        // Typo3SetList::UNDERSCORE_TO_NAMESPACE,
        // Typo3SetList::EXTBASE_COMMAND_CONTROLLERS_TO_SYMFONY_COMMANDS,
        // Typo3SetList::REGISTER_ICONS_TO_ICON,
        // Typo3SetList::NIMUT_TESTING_FRAMEWORK_TO_TYPO3_TESTING_FRAMEWORK,

        // SetList::CODE_QUALITY,
        // SetList::CODING_STYLE,
        // SetList::DEAD_CODE,
        // SetList::PSR_4,
        // SetList::TYPE_DECLARATION,
        // SetList::TYPE_DECLARATION_STRICT,
        // SetList::EARLY_RETURN,

        // PHPUnitSetList::PHPUNIT80_DMS,
        // PHPUnitSetList::PHPUNIT_40,
        // PHPUnitSetList::PHPUNIT_50,
        // PHPUnitSetList::PHPUNIT_60,
        // PHPUnitSetList::PHPUNIT_70,
        // PHPUnitSetList::PHPUNIT_80,
        // PHPUnitSetList::PHPUNIT_90,
        // PHPUnitSetList::PHPUNIT_91,
        // PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        // PHPUnitSetList::PHPUNIT_EXCEPTION,
        // PHPUnitSetList::REMOVE_MOCKS,
        // PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD,
        // PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER,
    ]);

    $rectorConfig->skip(
        [
            // This rector is over-zealous.
            CountOnNullRector::class,
            NameImportingPostRector::class => [
                // These files get concatenated by TYPO3, and we cannot use imports there.
                __DIR__ . '/Configuration/TCA',
                __DIR__ . '/ext_localconf.php',
            ],
        ]
    );
};
