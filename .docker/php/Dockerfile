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
    apt-get install -y vim wget git

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

# WKhtmltopdf

RUN apt-get install -y openssl build-essential libssl-dev libxrender-dev git-core libx11-dev libxext-dev libfontconfig1-dev libfreetype6-dev fontconfig libjpeg62-turbo xfonts-75dpi  xfonts-base
RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb -O /tmp/wkhtml2pdf.deb \
    && dpkg -i /tmp/wkhtml2pdf.deb \
    && ln -sn /usr/local/bin/wkhtmltopdf /bin/wkhtmltopdf \
    && rm -f /tmp/wkhtml2pdf.deb

# Firefox

RUN apt-get install -y firefox-esr

# Chromium

RUN apt-get install -y chromium

# Libreoffice

RUN apt-get install -y libreoffice

# Cleanups

RUN apt autoremove -y

WORKDIR /app
