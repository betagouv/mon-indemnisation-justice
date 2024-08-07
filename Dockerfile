FROM php:8.2-apache

# --- installation de composer
# TODO use multi-stage instead https://medium.com/@othillo/adding-composer-to-php-docker-images-using-multi-stage-builds-2a10967ae6c1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update -y && \
    apt-get install zip unzip
ENV COMPOSER_ALLOW_SUPERUSER=1
# --- installation de composer /

# --- installation de symfony
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y wget git && \
    wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
# --- installation de symfony /

# --- configuration de APACHE
# --- configuration de APACHE /

# --- configuration de php
RUN apt-get update -y && \
    apt-get install -y zlib1g-dev libicu-dev g++

RUN docker-php-ext-configure intl && docker-php-ext-install intl
RUN docker-php-ext-install calendar && docker-php-ext-configure calendar
RUN docker-php-ext-install gd && docker-php-ext-configure gd
# --- configuration de php /

# --- extension postgres
RUN apt-get update -y && apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql
# --- extension postgres /

# --- extension zip
RUN apt-get install -y libzip-dev zip && docker-php-ext-install zip
# --- extension zip /

# --- divers
RUN apt-get install -y vim
# --- divers /

# --- installation de npm & yarn
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g
# --- installation de yarn /

RUN apt autoremove -y

COPY .docker/apache/config/php.ini "$PHP_INI_DIR/php.ini"
COPY .docker/apache/config/000-default.conf /etc/apache2/sites-available/000-default.conf

RUN a2ensite 000-default.conf && \
    sed -i "s|Listen 80|Listen 8080|g" /etc/apache2/ports.conf && \
    service apache2 restart

# --- wkhtml2pdf
RUN apt-get update && \
    apt-get remove -y wkhtmltopdf && \
    apt-get autoremove -y
RUN apt-get install -y openssl build-essential libssl-dev libxrender-dev \
    git-core libx11-dev libxext-dev libfontconfig1-dev libfreetype6-dev fontconfig
RUN mkdir /var/wkhtmltopdf
RUN cd /var/wkhtmltopdf && \
    wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz && tar xf wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
RUN cp /var/wkhtmltopdf/wkhtmltox/bin/wkhtmltopdf /bin/wkhtmltopdf && \
    cp /var/wkhtmltopdf/wkhtmltox/bin/wkhtmltoimage /bin/wkhtmltoimage
RUN chown -R www-data:www-data /var/wkhtmltopdf
RUN chmod +x /bin/wkhtmltopdf && \
    chmod +x /bin/wkhtmltoimage
# --- wkhtml2pdf /

# --- pdftk
RUN apt-get update && \
    apt install -y pdftk
# --- pdftk /

# --- semaphore
RUN docker-php-ext-install sysvsem && \
    service apache2 restart
# --- semaphore /

###> ldap ###
RUN \
apt-get update && \
apt-get install libldap2-dev libldap-common -y && \
rm -rf /var/lib/apt/lists/* && \
docker-php-ext-configure ldap --with-libdir=lib/$(uname -m)-linux-gnu/ && docker-php-ext-install ldap
###< ldap ###


WORKDIR /var/www/html

ADD . /var/www/html

ARG APP_ENV
ENV APP_ENV ${APP_ENV:-prod}

RUN --mount=type=cache,target=/root/.cache/composer/ composer install

ENV YARN_CACHE_FOLDER=/root/.yarn
RUN --mount=type=cache,target=/root/.yarn yarn install
RUN --mount=type=cache,target=/root/.yarn yarn dev

EXPOSE 8080

