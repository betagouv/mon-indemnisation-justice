FROM pierrelemee/mij-frankenphp

COPY backend /app/

ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV

ARG APP_SECRET
ARG DATABASE_URL
ARG BASE_URL
ARG PRECONTENTIEUX_COURRIEL_EQUIPE
ARG SENTRY_DSN
ARG VITE_SENTRY_DSN

RUN composer install --no-ansi --no-dev --no-progress --optimize-autoloader

COPY frontend /tmp/frontend

WORKDIR /tmp/frontend

RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn && yarn install --prod --frozen-lockfile
RUN --mount=type=cache,target=/root/.vite VITE_CACHE_DIR=/root/.vite yarn build

RUN mv /tmp/frontend/build /app/public

WORKDIR /app/public

RUN rm -Rf /tmp/frontend

CMD ["frankenphp", "php-server", "-a", "--worker", "./index.php"]

