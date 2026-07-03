FROM pierrelemee/mij-frankenphp

COPY backend /app/

ARG APP_ENV=prod
ENV APP_ENV=$APP_ENV
ARG CC_COMMIT_ID
# Version actuellement déployée (calée sur le hash commmit donné par CleverCloud)
ARG MIJ_VERSION=$CC_COMMIT_ID
ENV MIJ_VERSION=$MIJ_VERSION
# Propagation à vite
ENV VITE_MIJ_VERSION=$MIJ_VERSION
# Token d'API pour Sentry (utilisé pour publier les sources lors du build)
ARG SENTRY_AUTH_TOKEN
# Propagation à vite
ENV VITE_SENTRY_AUTH_TOKEN=$SENTRY_AUTH_TOKEN

ARG DATABASE_URL
ARG PRECONTENTIEUX_COURRIEL_EQUIPE
ARG APP_SECRET
ARG BASE_URL
ARG SENTRY_DSN=""
ARG VITE_SENTRY_DSN
ARG VITE_CRISP_WEBSITE_ID

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

COPY ./.docker/php/scripts/demarrage_${APP_ENV}.sh /app/bin/demarrage.sh

RUN chmod u+x /app/bin/demarrage.sh

ENTRYPOINT ["/app/bin/demarrage.sh"]