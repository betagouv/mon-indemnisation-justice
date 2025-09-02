FROM pierrelemee/mij-frankenphp

COPY backend /app/

ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV

ARG DATABASE_URL
ARG PRECONTENTIEUX_COURRIEL_EQUIPE
ARG APP_SECRET
ARG BASE_URL
ARG SENTRY_DSN=""
ARG VITE_SENTRY_DSN

ARG COMPOSER_OPTS=--no-dev

WORKDIR /app

RUN composer install --no-ansi ${COMPOSER_OPTS} --no-progress --optimize-autoloader

COPY frontend /tmp/vite

WORKDIR /tmp/vite

RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn yarn install --prod --frozen-lockfile
RUN --mount=type=cache,target=/root/.vite VITE_CACHE_DIR=/root/.vite yarn build

RUN cp -r /tmp/vite/vite /app/public/

WORKDIR /app/public

RUN rm -Rf /tmp/vite/*

CMD ["/bin/bash", "-c", "/app/bin/console doctrine:migration:migrate --no-interaction --all-or-nothing && frankenphp php-server ./index.php"]

