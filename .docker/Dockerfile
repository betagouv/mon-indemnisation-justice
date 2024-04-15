FROM registry.anje-justice.fr/anje/anj-php:8.2-apache-pgsql-apcu

COPY .docker/config/php.ini "$PHP_INI_DIR/php.ini"

RUN rm /etc/apache2/sites-available/000-default.conf && rm /etc/apache2/sites-enabled/000-default.conf
COPY .docker/config/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Apache encore de la config
RUN rm -rf /var/www/html && \
    mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && \
    sed -i "s/Listen 80/Listen 8080/" /etc/apache2/ports.conf && \
    chmod 777 -R /var && \
    chmod 777 -R /run

WORKDIR /var/www/html

ADD . /var/www/html

RUN chmod 777 -R /var/www/html/public

EXPOSE 8080 80 443