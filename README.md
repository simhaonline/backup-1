# Backup Agent

A backup agent who creates backups of files and databases (incl. Docker container).  
Works together with the Backup Server as comprehensive backup tool.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

Requires installed **PHP 7.3** or above.  
The server should also be able to **send mails**.

### Installing

Place the Phar file somewhere on your disk.

## Deployment

Add an entry into the crontab to execute this script periodically.

```bash
0 4 * * * php /anywhere/on/your/disk/backup-agent.phar
```

In this example the script would run every day at 4am.

## Build With

* [PHP](https://www.php.net/)

## Authors

* [BloodhunterD](https://github.com/bloodhunterd)

## License

This project is licensed under the MIT - see [LICENSE.md](https://github.com/bloodhunterd/backup-agent/blob/master/LICENSE) file for details.
