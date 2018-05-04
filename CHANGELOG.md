# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

## x.y.z

### Added

### Changed

### Removed

### Fixed
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
