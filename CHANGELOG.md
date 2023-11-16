# Ticketsolve Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 1.0.0 - 2020-01-09
### Added
- Initial release

## 1.0.1 - 2020-01-09
### Changed
- Fixed repo URLs in composer.json

## 1.1.0 - 2020-01-21
### Changed
- Changed attribution to Burnthebook Ltd.
- Changed namespaces to burnthebook\ticketsolve
- Moved repository to https://github.com/Burnthebook/craft3-ticketsolve
- Moved composer package to burnthebook/craft3-ticketsolve

## 1.1.1 - 2020-01-21
### Fixed
- Fixed namespaces migration

## 1.1.2 - 2020-01-22
### Added
- Migration for updating Shows field namespace

## 1.2.0 - 2020-01-24
### Added
- Ability to filter events by dateTime, openingTime and onSaleTime
- Ability to filter shows by eventDateTime, eventOpeningTime and eventOnSaleTime
- Ability to order events by dateTime, openingTime and onSaleTime
- Ability to order shows by eventDateTime

## 1.2.1 - 2020-01-28
### Fixed
- Fixed a bug where Craft was wrongly counting the number of selected elements in a Shows field

## 1.2.2 - 2020-03-16
### Added
- Added a ten minute timeout using `set_time_limit(600)`

## 2.0.0 - 2023-11-10
- Craft 4 - initial compatibility

## 2.0.1 - 2023-11-16
- Fix PHP error `Getting unknown property: burnthebook\ticketsolve\fields\Shows::limit`
-- [limit seems to have changed to branchLimit](https://docs.craftcms.com/api/v4/craft-fields-baserelationfield.html#public-properties)

## 2.0.2 - 2023-11-16
- Updates ElementCountValidator