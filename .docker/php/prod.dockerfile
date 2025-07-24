FROM pierrelemee/mij-frankenphp

COPY . /app/

ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV

ARG APP_SECRET
ARG DATABASE_URL
ARG BASE_URL
ARG PRECONTENTIEUX_COURRIEL_EQUIPE
ARG SENTRY_DSN
ARG VITE_SENTRY_DSN

RUN composer install --no-ansi --no-dev --no-progress --optimize-autoloader

RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn yarn install --prod --frozen-lockfile
RUN --mount=type=cache,target=/root/.vite VITE_CACHE_DIR=/root/.vite yarn build

WORKDIR /app/public

CMD ["frankenphp", "php-server", "-a", "--worker", "./index.php"]

