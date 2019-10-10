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

Upload the backup-agent.phar.bz2 anywhere on your server and create a configuration file.

Add an entry into the crontab to execute this script periodically.

```bash
0 4 * * * php /anywhere/on/your/disk/backup-agent.phar.bz2 config.json
```

In this example the script would run every day at 4am.

The backup agent expecting the path to the configuration file as parameter.

## Build With

* [PHP](https://www.php.net/)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup-agent/blob/master/LICENSE) file for details.
