FROM registry.anje-justice.fr/anje/anj-php:8.2-apache-pgsql-apcu-wkhtmltopdf AS base

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

COPY .docker/apache/config/php.ini "$PHP_INI_DIR/php.ini"

RUN rm /etc/apache2/sites-available/000-default.conf && rm /etc/apache2/sites-enabled/000-default.conf
COPY .docker/apache/config/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Apache encore de la config
RUN rm -rf /var/www/html && \
    mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && \
    sed -i "s/Listen 80/Listen 8080/" /etc/apache2/ports.conf && \
    chmod 777 -R /var && \
    chmod 777 -R /run

# Installation de npm & yarn
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g

WORKDIR /var/www/html

ADD . /var/www/html

RUN chmod 777 -R /var/www/html/public

ARG APP_ENV
ENV APP_ENV ${APP_ENV:-prod}

RUN --mount=type=cache,target=/root/.cache/composer/ composer install

ENV YARN_CACHE_FOLDER=/root/.yarn
RUN --mount=type=cache,target=/root/.yarn yarn install
RUN --mount=type=cache,target=/root/.yarn yarn dev

EXPOSE 8080 80 443


