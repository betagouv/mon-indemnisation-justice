FROM dunglas/frankenphp:1.11.3-php8.4-trixie AS frankenphp

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
    apt-get install -y vim wget locales

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
    bcmath

# PHP config
RUN echo 'date.timezone = "Europe/Paris"' >> $PHP_INI_DIR/php.ini
RUN echo 'memory_limit = -1' >> $PHP_INI_DIR/php.ini
RUN echo 'upload_max_filesize = 20M' >> $PHP_INI_DIR/php.ini
RUN echo 'post_max_size = 30M' >> $PHP_INI_DIR/php.ini
RUN echo 'intl.default_locale = "fr"' >> $PHP_INI_DIR/php.ini

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
RUN apt-get install -y nodejs
RUN npm update -g
RUN npm install --force -g yarn

# Firefox
RUN apt install -y ca-certificates curl gnupg
RUN curl -fsSL https://packages.mozilla.org/apt/repo-signing-key.gpg | gpg --dearmor -o /usr/share/keyrings/packages.mozilla.org.gpg
RUN gpg --show-keys --with-fingerprint /usr/share/keyrings/packages.mozilla.org.gpg | head -n 2

RUN echo 'Types: deb \n\
URIs: https://packages.mozilla.org/apt \n\
Suites: mozilla \n\
Components: main \n\
Signed-By: /usr/share/keyrings/packages.mozilla.org.gpg\n' >> /etc/apt/sources.list.d/mozilla.sources

RUN cat /etc/apt/sources.list.d/mozilla.sources
RUN apt-get update -y && \
    apt-get install -y firefox
ENV FIREFOX_PATH=/usr/bin/firefox

# Installer pdf2text pour pouvoir extraire le texte d'un fichier PDF
RUN apt install -y poppler-utils

# Installer un client Postgre
RUN apt install -y postgresql-client
RUN echo 'alias database="psql \${DATABASE_URL/\?*/}"' >> /root/.bashrc

# Cleanups
RUN apt autoremove -y

ENV TZ=Europe/Paris
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_RUNTIME="Runtime\\FrankenPhpSymfony\\Runtime"

WORKDIR /app