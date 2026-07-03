FROM pierrelemee/mij-frankenphp

# Installer cron, supervisor et screen
RUN apt update -y && \
    apt upgrade -y --fix-missing && \
    apt install -y cron supervisor screen

COPY backend /app/

ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV
ARG CC_COMMIT_ID
# Version actuellement déployée (calée sur le hash commmit donné par CleverCloud)
ARG MIJ_VERSION=$CC_COMMIT_ID
ENV MIJ_VERSION=$MIJ_VERSION

ARG DATABASE_URL
ARG PRECONTENTIEUX_COURRIEL_EQUIPE
ARG APP_SECRET
ARG BASE_URL
ARG SENTRY_DSN=""

ARG COMPOSER_OPTS=--no-dev

WORKDIR /app

# Exporter les varianles d'environnement dans le fichier dotenv de l'environnement applicatif
RUN test -f "/app/.env.${APP_ENV}" || env | grep -E 'APP_DEBUG|APP_ENV|APP_SECRET|BASE_URL|DATABASE_URL|EMAIL_FROM|EMAIL_FROM_LABEL|MAILER_DSN|PRECONTENTIEUX_COURRIEL_EQUIPE|SCW_ACCESS_KEY|SCW_BUCKET|SCW_DEFAULT_ORGANIZATION_ID|SCW_DEFAULT_PROJECT_ID|SCW_HOST|SCW_REGION|SCW_SECRET_KEY|SENTRY_DSN' > "/app/.env.${APP_ENV}"

RUN echo /app/.env.${APP_ENV}
RUN cat /app/.env.${APP_ENV}

# Installer les dépendances composer
RUN --mount=type=cache,target=/root/.cache/composer COMPOSER_CACHE_DIR=/root/.cache/composer composer install --no-ansi ${COMPOSER_OPTS} --no-progress --optimize-autoloader

# Installer les dépendances javascript (puppeteer pour l'impression PDF)
RUN yarn install

# Configuration des tâches cron
RUN cat <<EOF > /etc/cron.d/taches-mij-job
17 5 * * * root /app/bin/console mij:importer:geo
31 5 * * * root /app/bin/console mij:importer:fdo
EOF

RUN chmod 0600 /etc/cron.d/taches-mij-job

# Configuration de supervisor
COPY <<EOF /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
logfile=/var/log/supervisord.log
pidfile=/var/run/supervisord.pid

[program:cron]
command=/usr/sbin/cron -f
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stderr_logfile=/dev/stderr
EOF

# Lancer supervisor au démarrage
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]