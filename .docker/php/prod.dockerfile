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

RUN --mount=type=cache,target=/root/.cache/composer COMPOSER_CACHE_DIR=/root/.cache/composer composer install --no-ansi ${COMPOSER_OPTS} --no-progress --optimize-autoloader
RUN yarn install

COPY frontend/package.json frontend/yarn.lock /app/assets/

WORKDIR /app/assets

RUN --mount=type=cache,target=/root/.yarn YARN_CACHE_FOLDER=/root/.yarn yarn install --prod --frozen-lockfile

COPY frontend /app/assets

RUN --mount=type=cache,target=/root/.vite VITE_CACHE_DIR=/root/.vite yarn build --outDir=/app/public/build --base=/build

RUN rm -Rf /assets/*

WORKDIR /app

CMD ["bash", "-c", "frankenphp php-server -r /app/public -a /app/public/index.php"]