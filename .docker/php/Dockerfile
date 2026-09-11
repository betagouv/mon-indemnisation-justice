FROM dunglas/frankenphp:1.12.7-php8.5-trixie AS frankenphp

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