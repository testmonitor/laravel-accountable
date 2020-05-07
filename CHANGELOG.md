# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [4.2.0] - 2020-05-07
### Added
- Anonymous user option

## [4.1.0] - 2020-03-18
### Added
- Enable/disable method

### Changed
- Internal configuration management
- Revised linter configuration

## [4.0.0] - 2020-03-07
### Changed
- Requires Laravel 6/7

## [3.1.0] - 2019-09-06
### Changed
- Requires Laravel 6

## [3.0.0] - 2019-07-14
### Updated
- TestMonitor rebrand

## [2.1.0] - 2019-05-03
### Added
- Support for disabling accountable

### Changed
- Updated CircleCI to 2.0

## [2.0.0] - 2018-09-19
### Added
- Support for trashed user model

### Changed
- Requires Laravel 5.5 or up

## [1.1.1] - 2017-10-04
### Fixed
- Fixed user model detection

## [1.1.0] - 2017-09-23
### Added
- Support for auto-loading service provider in Laravel 5.5  

## [1.0.3] - 2017-05-18
### Fixed
- Accountable observer blocks other Laravel model listeners/observers 

## [1.0.2] - 2017-04-26
### Changed
- CreatedByUser / UpdatedByUser / DeletedByUser have been changed to CreatedBy / UpdatedBy / DeletedBy.
- Scope queries have been prefixed with only, i.e. onlyCreatedBy

## [1.0.1] - 2017-04-25
### Changed
- "Updated by user" will be set on model create.

## [1.0.0] - 2017-04-24
### Added
- Initial version.

