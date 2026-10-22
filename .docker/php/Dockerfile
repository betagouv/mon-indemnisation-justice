FROM dunglas/frankenphp:1.13-php8.5.12-trixie AS frankenphp

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc
RUN echo "export LC_CTYPE=fr_FR.UTF-8" >> /root/.bashrc
RUN echo "export LC_ALL=fr_FR.UTF-8" >> /root/.bashrc

# Generic utils
RUN apt-get update -y && \
    apt-get upgrade -y --fix-missing && \
    apt-get install -y vim wget locales && \
    apt-get install -y libgtk-3-0 libasound2 libx11-xcb1 # Librairies requises pour que Firefox (installé par puppeteer) fonctionne

# Configurer la langue Française 🇫🇷
RUN sed -i '/fr_FR.UTF-8/s/^#//g' /etc/locale.gen
RUN locale-gen fr_FR.UTF-8

# PHP extensions
RUN install-php-extensions \
    @composer \
    zip \
    intl \
    calendar \
    pdo pdo_pgsql pgsql \
    bcmath \
    gd

# PHP config
RUN echo 'date.timezone = "Europe/Paris"' >> $PHP_INI_DIR/php.ini
RUN echo 'memory_limit = -1' >> $PHP_INI_DIR/php.ini
RUN echo 'upload_max_filesize = 20M' >> $PHP_INI_DIR/php.ini
RUN echo 'post_max_size = 30M' >> $PHP_INI_DIR/php.ini
RUN echo 'intl.default_locale = "fr"' >> $PHP_INI_DIR/php.ini

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash -
RUN apt-get install -y nodejs
# Contournement de l'erreur https://github.com/nodejs/node/issues/62425#issuecomment-4200715930
RUN npm install -g npm@11
RUN npm install --force -g yarn

# Installer pdf2text pour pouvoir extraire le texte d'un fichier PDF
RUN apt install -y poppler-utils

# Installer un client Postgre
RUN apt install -y postgresql-client
RUN echo 'alias database="psql \${DATABASE_URL/\?*/}"' >> /root/.bashrc

# Cleanups
RUN apt autoremove -y

ENV TZ=Europe/Paris
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

FROM frankenphp AS dev

ENV APP_ENV=dev

# Hors de /app pour ne pas être masqué par le volume `./backend:/app` en dev
COPY .docker/php/demarrage.sh /usr/local/bin/demarrage.sh

RUN chmod +x /usr/local/bin/demarrage.sh

ENTRYPOINT ["/usr/local/bin/demarrage.sh"]

CMD ["frankenphp", "php-server", "-r", "/app/public", "-a", "/app/public/index.php"]

FROM dev AS test

ENV APP_ENV=test

COPY backend /app

RUN --mount=type=cache,target=/root/.cache/composer COMPOSER_CACHE_DIR=/root/.cache/composer composer install --no-ansi ${COMPOSER_OPTS} --no-progress --optimize-autoloader
# Installer les dépendances javascript (puppeteer & firefox pour l'impression PDF)
RUN yarn install && npx puppeteer browsers install firefox

COPY frontend/package.json frontend/yarn.lock /app/assets/

WORKDIR /app/assets

RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn yarn install --prod --frozen-lockfile

COPY frontend /app/assets

RUN --mount=type=cache,target=/root/.vite VITE_CACHE_DIR=/root/.vite yarn build --outDir=/app/public/build --base=/build

RUN rm -Rf /app/assets

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]

WORKDIR /app

