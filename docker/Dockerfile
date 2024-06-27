FROM anj/anj-php:8.2-apache-pgsql-apcu-wkhtmltopdf

COPY php.ini "$PHP_INI_DIR/php.ini"

RUN rm /etc/apache2/sites-available/000-default.conf && rm /etc/apache2/sites-enabled/000-default.conf
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# Apache encore de la config
RUN rm -rf /var/www/html && \
    mkdir -p /var/lock/apache2 /var/run/apache2 /var/log/apache2 /var/www/html && \
    sed -i "s/Listen 80/Listen 8080/" /etc/apache2/ports.conf && \
    chmod 777 -R /var && \
    chmod 777 -R /run

WORKDIR /var/www/html

ADD se-mistral-webapp.tar /var/www/html

RUN rm -Rf /var/www/html/var && \
    mkdir -p /var/www/html/var/cache && mkdir -p /var/www/html/var/log && \
    chmod 777 -R /var/www/html/var/cache && chmod 777 -R /var/www/html/var/log && \
    chmod 777 -R /var/www/html/public

EXPOSE 8080
