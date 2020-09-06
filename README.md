[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?include_prereleases&style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
[![Build](https://img.shields.io/travis/bloodhunterd/backup?style=for-the-badge)](https://travis-ci.com/github/bloodhunterd/backup)
[![PHP](https://img.shields.io/badge/PHP-%5E7.3-blue?style=for-the-badge)](https://github.com/bloodhunterd/backup-tool/blob/master/build/backup.phar)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%207-blueviolet?style=for-the-badge)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

# Backup

A simple application to back up files, databases and download them securely.

## Features

* Simple configuration
* Dump docker databases
* Strong compression
* Encrypted downloads
* Shows backup size and duration

## Prerequisites

### Agent

* A Linux distribution
* PHP >= **7.3**
* PHP extensions
  * BZ2
  * CLI
  * INTL
  * JSON

### Manager

* All Agent requirements
* [OpenSSH](https://www.openssh.com/) client
* [rsync](https://linux.die.net/man/1/rsync)

### Optional

* A Mail Transfer Agent like [Exim](https://www.exim.org/) or [Postfix](http://www.postfix.org/) to send reports.

## Deployment

Download the Phar file and place it somewhere on your server.
For example at **/srv/**.

[![Backup App](https://img.shields.io/badge/Download-Backup%20App-blue?style=for-the-badge)](https://github.com/bloodhunterd/backup-tool/blob/master/build/backup.phar)

Download the distributed agent and manager configuration files and place it somewhere on your server. For example also at **/srv/**.

[![Agent Configuration](https://img.shields.io/badge/Download-Agent%20Configuration-blue?style=for-the-badge)](https://github.com/bloodhunterd/backup-tool/blob/master/dist/agent.dist.json)
[![Manager Configuration](https://img.shields.io/badge/Download-Manager%20Configuration-blue?style=for-the-badge)](https://github.com/bloodhunterd/backup-tool/blob/master/dist/manager.dist.json)

Adjust the configuration file for your needs and add an entry into the Cron table to execute this application periodically.

```bash
0 4 * * * php /srv/backup.phar /srv/configuration.json
```

*In this example the backup runs every night at 4am.*

A good start is to enable the debugging mode in configuration and run the backup manually to ensure everything works fine.

## Update

Please note the [changelog](https://github.com/bloodhunterd/backup/blob/master/CHANGELOG.md) to check for configuration changes before updating.

## Build with

* [PHP](https://www.php.net/)
* [Monolog](https://github.com/Seldaek/monolog)
* [Vection Framework](https://github.com/Vection-Framework/Vection)
  * [DI-Container](https://github.com/Vection-Framework/DI-Container)
  * [Validator](https://github.com/Vection-Framework/Validator)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup/blob/master/LICENSE) file for details.
