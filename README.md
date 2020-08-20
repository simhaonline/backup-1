[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?include_prereleases&style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-blueviolet?style=for-the-badge)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

# Backup

A simple backup application who creates backups of files and databases (incl. Docker container).

## Prerequisites

* [Composer](https://getcomposer.org/)

* [PHP](https://www.php.net/) >= **7.3**

* PHP Extensions
  * php-bz2
  * php-cli
  * php-intl
  * php-json

**To send reports** *(optional)* :

* [MTA](https://de.wikipedia.org/wiki/Mail_Transfer_Agent)

## Installation

Install the dependencies simply by using Composer.

```bash
composer install
```

## Deployment

**This repository isn't meant to be deployed anywhere!**

For production purposes use the compiled Phar file from the [Backup Tool Repository](https://github.com/bloodhunterd/backup-tool) or
the [Docker](https://www.docker.com/) image from the [Backup Tool Docker Repository](https://github.com/bloodhunterd/backup-tool-docker).

## Update

Please note the [changelog](https://github.com/bloodhunterd/backup/blob/master/CHANGELOG.md) to check for configuration changes before updating.

## Build With

* [PHP](https://www.php.net/)
* [Monolog](https://github.com/Seldaek/monolog)
* [Vection Framework](https://github.com/Vection-Framework/Vection)
  * [DI-Container](https://github.com/Vection-Framework/DI-Container)
  * [Validator](https://github.com/Vection-Framework/Validator)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup/blob/master/LICENSE) file for details.
