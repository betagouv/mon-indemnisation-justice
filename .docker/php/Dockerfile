FROM composer:2.7 AS composer

FROM php:8.2-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

# Generic utils
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y vim wget git

# PHP extensions
#RUN apt-get install -y zlib1g-dev  g++   libfreetype6-dev libjpeg62-turbo-dev
RUN apt-get install -y libzip-dev zip && docker-php-ext-install zip
RUN apt-get install -y libicu-dev && docker-php-ext-install intl
RUN docker-php-ext-install calendar
RUN apt-get install -y libpng-dev && docker-php-ext-install gd
RUN apt-get install -y libpq-dev && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && docker-php-ext-install pdo pdo_pgsql pgsql
#RUN docker-php-ext-install sysvsem
RUN apt-get install -y libldap2-dev libldap-common && docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/ && docker-php-ext-install ldap

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g

# Chromium

RUN apt-get install -y chromium

# Libreoffice

RUN apt-get install -y libreoffice

# Cleanups

RUN apt autoremove -y

# Copy files & install dependencies
ADD . /app

WORKDIR /app

ARG APP_ENV
ENV APP_ENV ${APP_ENV:-dev}
ARG APP_DEBUG
ENV APP_DEBUG ${APP_DEBUG:-1}
ARG DATABASE_URL
ENV DATABASE_URL ${DATABASE_URL:-}

# Installer les dépendances PHP via `composer`:
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/root/.composer
RUN --mount=type=cache,target=/root/.composer composer install --no-interaction --optimize-autoloader

# Installer les dépendances Node via `yarn`:
ENV YARN_CACHE_FOLDER=/root/.yarn
RUN --mount=type=cache,target=/root/.yarn yarn install --frozen-lockfile
RUN --mount=type=cache,target=/root/.yarn yarn dev

# Surcharge de la commande Docker pour jouer les migrations avant de démarrer le serveur
CMD bin/console doctrine:migration:migrate --no-interaction --all-or-nothing && php-fpm



