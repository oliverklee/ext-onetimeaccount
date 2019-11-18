# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added
- Add support for TYPO3 9LTS (#123)
- Add code sniffing and fixing (#120)
- Build with PHP 7.2 on Travis CI (#115)
- Display the name of the current functional test (#97, #98)

### Changed
- Use the new configuration check (#122, #79)
- Bump the `static_info_tables` dependency (#121)
- Require oelib 3.0 (#119)
- Use the EXT: notation for language files (#76, #117)
- Sort the Composer dependencies (#111)
- Allow 9.5-compatible versions of mkforms and rn_base (#108)
- Update the testing libraries (#93, #96)

### Removed
- Drop unneeded Travis CI configuration settings (#99, #100, #101)
- Drop support for PHP 5 (#92, #114)
- Drop support for TYPO3 7.6 (#91, #104, #113)

### Fixed
- Avoid accessing the logger in the tests (#126)
- Move the plugin registration to `Configuration/TCA/Overrides/` (#125)
- Fix the path to the content element icon (#124)
- git-ignore the tests-generated `var/log/` folder (#118)
- Allow an empty name if the field is hidden or non-required (#105, #106)
- Add a dependency on typo3/cms-lang (#86, #94)

## 3.0.2

### Changed
- Simplify the dependency versions in composer.json (#87)
- Use the nimut TF for creating test records (#83)
- Update the oelib dependency (#80)
- Convert the tests to nimut/testing-framework (#74)
- Use CamelCase in the PHP namespace (#73)
- Upgrade to PHPUnit 5.7 (#71)
- Update the mkforms dependency (#70)
- Change from GPL V3+ to GPL V2+ (#67)
- Require oelib >= 2.3.0 (#63)
- Use spaces for indenting HTML and .htaccess files (#60)
- Streamline ext_emconf.php (#58)

### Removed
- Drop the TYPO3 package repository from composer.json (#72)

### Fixed
- Show the name label if first and last name are visible (#64, #89)
- Drop surplus trailing slashes from Composer scripts (#77, #88)
- Add the transitive dependencies to composer.json (#85)
- Use the ConnectionPool for database queries in TYPO3 8LTS (#84)
- Use the correct name for the extension icon file (#82)
- Keep development files out of the packages (#81)
- Clean up and lint the TypoScript (#75)
- Pin the dev dependency versions (#88)
- Enable MySQL on Travis CI (#66)
- Explicitly provide the extension name in the composer.json (#85)
- Also provide the extension icon in `Resources/` (#62)
- Add a dependency to felogin (#59)

## 3.0.1

### Added
- Auto-release to the TER (#48)

### Changed
- Move the old tests to Tests/LegacyUnit/ (#55)

### Fixed
- Drop an obsolete "replace" entry from composer.json (#68)
- Don't HTML-encode the data from the FE editor on saving (#56)
- Use the current composer names of static_info_tables (#54)
- Add a conflict with a PHP-7.0-incompatible static_info_tables version (#51)
- Update the composer package name of static-info-tables (#47)
- Stop PHP-linting the removed Migrations/ folder (#46)
- Use the public mkforms render method (#45)

## 3.0.0

### Added
- Add support for TYPO3 8.7 (#41)
- Add support for PHP 7.1 and 7.2 (#36)

### Changed
- Also allow oelib 3.x (#43)
- Switch to the SVG icon in the BE for the content element (#38)
- Require oelib >= 2.0.0 (#35)
- Require static_info_tables >= 6.4.0 (#33)
- Update to PHPUnit 5.3 (#31)

### Removed
- Remove the class alias map (#38)
- Drop ext_autoload.php (#34)
- Require TYPO3 7.6 and drop support for TYPO3 6.2 (#32)
- Drop support for PHP 5.5 (#29)

### Fixed
- Update the content element wizard for TYPO3 8.7 (#42)
- Fix the redirect_url on TravisCI on TYPO3 8.7 (#40)
- Make the PHPUnit test runner configurable (#37)

## 2.1.0

### Added
- Run the unit tests on TravisCI (#12)
- Add an SVG extension icon (#15)
- Composer script for PHP linting (#5)
- Add TravisCI builds

### Changed
- Require oelib ^1.4.0 (#21)
- Require mkforms >= 3.0.0 (#4)
- Require static_info_tables >= 6.3.7 (#2)
- Move the extension to GitHub

### Removed
- Drop the incorrect TYPO3 Core license headers (#20)
- Remove the example extension (#6)

### Fixed
- Fix PhpStorm code inspection warnings (#27)
- Use a public method instead of the internal _getThisFormData (#25)
- Update use of deprecated rn_base configuration class (#24)
- Fix the unit tests concerning the redirect URL on CLI (#22)
- Provide cli_dispatch.phpsh for 8.7 on Travis (#18)
- Require typo3/minimal for installing TYPO3 (#17)
- Require mkforms >= 3.0.14 (#14)
- Fix autoloading when running the tests in the BE module in non-composer mode (#11)
- Use the correct package name for mkforms in composer.json
- Allow mkforms 3.x

## 2.0.0

The [change log up to version 2.0.0](Documentation/changelog-archive.txt)
has been archived.
