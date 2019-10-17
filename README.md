[![Build Status](https://img.shields.io/travis/bloodhunterd/backup?style=for-the-badge)](https://travis-ci.com/bloodhunterd/backup)
[![Docker Build](https://img.shields.io/docker/cloud/build/bloodhunterd/backup?style=for-the-badge)](https://hub.docker.com/r/bloodhunterd/backup)
[![Release](https://img.shields.io/github/v/release/bloodhunterd/backup?include_prereleases&style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)
[![License](https://img.shields.io/github/license/bloodhunterd/backup?style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/LICENSE)

# Backup

A simple backup application who creates backups of files and databases (incl. Docker container).

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
See deployment for notes on how to deploy the project on a live system.

### Prerequisites

PHP including the following modules are required to run the application.

* php-bz2¹
* php-cli
* php-intl
* php-json

The server should also have a working MTA to send backup reports.

¹ only needed if the compressed version is used.

### Installing

Download the (compressed) backup application and place it somewhere on your server. For example at **/srv/**.

[![Phar](https://img.shields.io/github/size/bloodhunterd/backup/build/backup.phar?label=Backup&style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/build/backup.phar)
[![Phar BZ2](https://img.shields.io/github/size/bloodhunterd/backup/build/backup.phar.bz2?label=Backup%20(compressed)&style=for-the-badge)](https://github.com/bloodhunterd/backup/blob/master/build/backup.phar.bz2)

Download the example agent or manager configuration file and place it somewhere on your server. For example also at **/srv/**.

## Deployment

Adjust the configuration file for your needs and add an entry into the crontab to execute this script periodically.

```bash
0 4 * * * php /srv/backup.phar /srv/backup.json
```

In this example the backup would run every night at 4am.

## Build With

* [PHP](https://www.php.net/)
* [Vection](https://github.com/Vection-Framework/Vection)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup-agent/blob/master/LICENSE) file for details.
