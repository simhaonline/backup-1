# Changelog

All notable changes to this project will be documented in this file.

## [Master](https://github.com/bloodhunterd/backup) - 18.11.2020

* Merged together with docker repository
* Added email validation
* Resolved issue #8 Execute command before and after backup
* Added Ko-fi link
* Fixed issue #11 Wrong display of seconds in duration time

## <a name="v0-7-0"></a> [0.7.0](https://github.com/bloodhunterd/backup/releases/tag/0.7.0) - 11.10.2020

* Issue #7 Convert size and duration in a unit which fit best fixed
* Issue #6 Report: Show debugging info only if at least one error occurred fixed
* Template engine Twig implemented to render report mail
* File logging removed (see [README.md](https://github.com/bloodhunterd/backup/blob/master/README.md) for alternative)

## <a name="v0-6-1"></a> [0.6.1](https://github.com/bloodhunterd/backup/releases/tag/0.6.1) - 06.09.2020

* Archive size and backup time added
* Log and report messages fixed

## <a name="v0-6-0"></a> [0.6.0](https://github.com/bloodhunterd/backup/releases/tag/0.6.0) - 05.09.2020

* Report layout overworked *(sections and emojis included)*
* Log messages overworked
* Backup type in report fixed
* Merged with compiled version repository *(fka Backup Tool)*

## <a name="v0-5-1"></a> [0.5.1](https://github.com/bloodhunterd/backup/releases/tag/0.5.1) - 30.08.2020

* Log and report messages adjusted
* Column order in report adjusted
* Wrong type in report fixed

## <a name="v0-5-0"></a> [0.5.0](https://github.com/bloodhunterd/backup/releases/tag/0.5.0) - 29.08.2020

* Info status and message for log and report added
* Colors of report updated
* Issue #5 Backup breaks on not supported language fixed
* Issue #4 No error in backup report on failure fixed

## <a name="v0-4-2"></a> [0.4.2](https://github.com/bloodhunterd/backup/releases/tag/0.4.2) - 24.08.2020

* Fix language utilization

## <a name="v0-4-1"></a> [0.4.1](https://github.com/bloodhunterd/backup/releases/tag/0.4.1) - 20.08.2020

* Fix language settings due removing allowed values in configuration schema.

## <a name="v0-4-0"></a> [0.4.0](https://github.com/bloodhunterd/backup/releases/tag/0.4.0) - 03.11.2019

* Send a report for processed backups
* Backup all database schemata (Docker)
* Usage of MySQL environment vars for user and password (Docker)
* Configuration validation
* Database backups with no password
* Disable directory and database backups
* Disable server downloads
* PHPStan Level 7
