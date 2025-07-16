FROM composer:2.7 AS composer

FROM dunglas/frankenphp:1.8-php8.3-bookworm AS frankenphp

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

# Generic utils
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y vim wget

# PHP extensions
RUN install-php-extensions \
    zip \
    intl \
    calendar \
    pdo pdo_pgsql pgsql

# Symfony CLI

#RUN curl -1sLf https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh | bash && \
#    apt install -y symfony-cli

# PHP config
RUN echo 'date.timezone = "Europe/Paris"' >> $PHP_INI_DIR/php.ini
RUN echo 'memory_limit = -1' >> $PHP_INI_DIR/php.ini
RUN echo 'upload_max_filesize = 10M' >> $PHP_INI_DIR/php.ini
RUN echo 'post_max_size = 100M' >> $PHP_INI_DIR/php.ini

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g
RUN npm install --force -g yarn

# Chrome
RUN apt install -y chromium

# Cleanups

RUN apt autoremove -y

WORKDIR /app

FROM frankenphp AS prod

COPY . /app/

RUN  ls -la .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --optimize-autoloader

RUN yarn install --frozen-lockfile && yarn build && yarn vite build

WORKDIR /app/public

CMD ["frankenphp", "php-server", "-a", "--worker", "./index.php"]