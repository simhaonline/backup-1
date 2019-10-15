[![Build Status](https://img.shields.io/travis/bloodhunterd/backup?style=for-the-badge)](https://travis-ci.com/bloodhunterd/backup)
[![Release](https://img.shields.io/github/v/tag/bloodhunterd/backup?include_prereleases&style=for-the-badge)](https://github.com/bloodhunterd/backup/releases)

# Backup

The backup agent who creates backups of files and databases (incl. Docker container).
Works together with the backup server as comprehensive backup tool.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.
See deployment for notes on how to deploy the project on a live system.

### Prerequisites

To execute the backup tool **PHP 7.3** or above is required.
Additionally the following modules are also required.

* php-bz2
* php-cli
* php-intl
* php-json

The server should also be able to send mails.

### Installing

Place the Phar file somewhere on your disk.

## Deployment

Upload the backup-agent.phar anywhere on your server and create a configuration file.

Add an entry into the crontab to execute this script periodically.

```bash
0 4 * * * php /path/to/backup-agent.phar /path/to/backup-agent.conf.json
```

In this example the script would run every day at 4am.

The backup agent expecting the absolute path to the configuration file as parameter.

## Build With

* [PHP](https://www.php.net/)
* [Vection](https://github.com/Vection-Framework/Vection)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup-agent/blob/master/LICENSE) file for details.
