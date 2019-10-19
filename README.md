[![Build Status](https://img.shields.io/travis/bloodhunterd/backup?style=for-the-badge)](https://travis-ci.com/bloodhunterd/backup)
[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?include_prereleases&style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

# Backup

A simple backup application who creates backups of files and databases (incl. Docker container).

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* Composer

PHP version **7.2** or greater is required, including the following modules. 

* php-bz2
* php-cli
* php-intl
* php-json

The server should also have a working MTA to send backup reports.

### Installing

Install the dependencies simply with Composer.

```bash
composer install
```

## Deployment

This repository isn't meant to be deployed anywhere.

For production use the compiled Phar file of the [Backup Tool Repository](https://github.com/bloodhunterd/backup-tool) or
the Docker image of the [Backup Tool Docker Repository](https://github.com/bloodhunterd/backup-tool-docker).

## Build With

* [PHP](https://www.php.net/)
* [Vection](https://github.com/Vection-Framework/Vection)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup-agent/blob/master/LICENSE) file for details.
