ARG PHP_RUNTIME=7.4

# Get the base image to get started
FROM php:$PHP_RUNTIME-cli AS vies-base
ARG PHP_RUNTIME=7.4

RUN apt-get update \
  && apt-get install -y \
    git \
    libbz2-dev \
    libicu-dev \
    libpng-dev \
    libsqlite3-dev \
    libtidy-dev \
    libxml2-dev \
    libxslt1-dev \
    libzip-dev \
    sqlite3 \
    unzip

# Install required PHP components
FROM vies-base AS vies-php
RUN docker-php-ext-install -j$(nproc) \
    bcmath \
    bz2 \
    calendar \
    exif \
    gd \
    gettext \
    intl \
    mysqli \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_sqlite \
    soap \
    sockets \
    tidy \
    xsl \
    zip

# Create a user and group to run the application
FROM vies-php AS vies-user
ARG GID=1001
ARG UID=1001
ARG VIES_NAME=vies-web
ARG VIES_HOME=/var/run/$VIES_NAME

RUN groupadd -g $GID $VIES_NAME
RUN useradd -g $GID -d $VIES_HOME -m -s /bin/bash $VIES_NAME
USER $VIES_NAME

# Copy source code and run the application
FROM vies-user AS vies-web
ARG VIES_PORT=18080
ARG VIES_ROOT=web
ARG VIES_NAME=vies-web
ARG VIES_HOME=/var/run/$VIES_NAME

LABEL com.dragonbe.package="VIES Web Application"
LABEL com.dragonbe.author="Michelangelo van Dam"
LABEL version="1.0"

WORKDIR $VIES_HOME
COPY . $VIES_HOME
ENTRYPOINT ["php", "-S", "0.0.0.0:18080", "-t", "web"]
EXPOSE $VIES_PORT
