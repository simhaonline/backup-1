FROM debian:stable-slim

# Time and location
ENV TZ=Europe/Berlin
ENV LOCALE="de_DE.UTF-8 UTF-8"

# PHP
ENV PHP_VERSION=7.3

# Cron
ENV CRON_MINUTE=0
ENV CRON_HOUR=3

# Mail
ENV SMTP_HOST=localhost
ENV SMTP_PORT=25
ENV SMTP_DOMAIN=
ENV SMTP_MAILDOMAIN=
ENV SMTP_FROM=
ENV SMTP_AUTH=off
ENV SMTP_USER=
ENV SMTP_PASSWORD=
ENV SMTP_TLS=on
ENV SMTP_STARTTLS=off
ENV SMTP_CERTCHECK=on

# Update and upgrade package repositories
RUN apt-get update && apt-get upgrade -y --no-install-recommends

# Install required packages
RUN apt-get install -y --no-install-recommends \
    locales \
    cron \
    rsync \
    msmtp \
    ca-certificates \
    openssh-client \
    sshpass \
    php${PHP_VERSION}-bz2 \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-json

# Create backup directory
RUN mkdir /backup

# Copy backup manager into directory
COPY ./build/backup-server.phar         /srv/
COPY ./build/backup-server.conf.json    /srv/

COPY ./start.sh /

# Set start script as entry point
ENTRYPOINT ["bash", "/start.sh"]
