FROM composer:2.7 AS composer

FROM php:8.3-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

# Generic utils
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y vim wget git openssl ca-certificates

# PHP extensions
RUN apt-get install -y libzip-dev zip && docker-php-ext-install zip
RUN apt-get install -y libicu-dev && docker-php-ext-install intl
RUN docker-php-ext-install calendar
RUN apt-get install -y libpng-dev && docker-php-ext-install gd
RUN apt-get install -y libpq-dev && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && docker-php-ext-install pdo pdo_pgsql pgsql
RUN apt-get install -y libldap2-dev libldap-common && docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/ && docker-php-ext-install ldap

# PHP config
RUN echo "memory_limit = -1" >> $PHP_INI_DIR/conf.d/php.ini

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g
RUN npm install --force -g yarn

# Firefox
RUN apt install -y firefox-esr

# Chrome
RUN apt install -y libvulkan1 fonts-liberation libgbm1 xdg-utils \
    && wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -O /tmp/google-chrome-stable_current_amd64.deb \
    && dpkg -i /tmp/google-chrome-stable_current_amd64.deb \
    && rm -f /tmp/google-chrome-stable_current_amd64.deb

# Cleanups

RUN apt autoremove -y

WORKDIR /app
