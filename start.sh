#!/bin/bash

# Set timezone
ln -snf "/usr/share/zoneinfo/${TZ}" etc/localtime && echo "${TZ}" > /etc/timezone

# Set locales
echo "${LOCALE}" >> /etc/locale.gen && locale-gen

# Set SendMail path
echo "sendmail_path = /usr/bin/msmtp -t" >> /etc/php/${PHP_VERSION}/cli/php.ini

# Create cronjob
echo "${CRON_MINUTE} ${CRON_HOUR} * * * php /srv/backup-server.phar backup-server.conf.json > /dev/null 2>&1" > /etc/cron.d/backup-server

# Write Cron job to Cron table
crontab /etc/cron.d/backup-server

# Create mSMTP configuration
cat << mSMTP > /root/.msmtprc
defaults
auth $SMTP_AUTH
tls $SMTP_TLS
tls_starttls $SMTP_STARTTLS
tls_certcheck $SMTP_CERTCHECK
tls_trust_file /etc/ssl/certs/ca-certificates.crt
account default
add_missing_from_header on
logfile ~/.msmtp.log
host "$SMTP_HOST"
port $SMTP_PORT
domain "$SMTP_DOMAIN"
maildomain "$SMTP_MAILDOMAIN"
user $SMTP_USER
password "$SMTP_PASSWORD"
from "$SMTP_FROM"
mSMTP

# Run in Cron in foreground
cron -f
