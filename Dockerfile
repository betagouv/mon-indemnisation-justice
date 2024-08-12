FROM composer:2.7 AS composer

FROM php:8.2-apache

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

# Generic utils
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y vim wget git libpq-dev pdftk

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g

# PHP extensions
RUN apt-get install -y zlib1g-dev libicu-dev g++ libpq-dev libzip-dev zip libldap2-dev libldap-common libfreetype6-dev libjpeg62-turbo-dev libpng-dev
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl
RUN docker-php-ext-install calendar
RUN docker-php-ext-install gd
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && docker-php-ext-install pdo pdo_pgsql pgsql
RUN docker-php-ext-install sysvsem
RUN docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/ && docker-php-ext-install ldap

# Wkhtml2pdf

RUN apt-get install -y openssl build-essential libssl-dev libxrender-dev git-core libx11-dev libxext-dev libfontconfig1-dev libfreetype6-dev fontconfig libjpeg62-turbo xfonts-75dpi  xfonts-base
RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb -O /tmp/wkhtml2pdf.deb && dpkg -i /tmp/wkhtml2pdf.deb && rm -f /tmp/wkhtml2pdf.deb

# Apache

COPY ./.docker/apache/config/php.ini "$PHP_INI_DIR/php.ini"
COPY ./.docker/apache/config/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default.conf && sed -i "s|Listen 80|Listen 8080|g" /etc/apache2/ports.conf

# Cleanups

RUN apt autoremove -y

# Copy files & install dependencies
ADD . /var/www/html

WORKDIR /var/www/html

# Why is that necessary ?
RUN chmod 777 -R /var/www/html/public

# /root/.cache/composer ?
COPY ./.docker/apache/cache/composer /root/composer
ARG APP_ENV
ENV APP_ENV ${APP_ENV:-prod}
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/root/.composer
RUN composer install

COPY ./.docker/apache/cache/yarn /root/.yarn
ENV YARN_CACHE_FOLDER=/root/.yarn
RUN yarn install --frozen-lockfile
RUN yarn dev

EXPOSE 8080 80 443


