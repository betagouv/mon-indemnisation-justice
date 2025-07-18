FROM pierrelemee/mij-frankenphp

COPY . /app/

ARG APP_ENV
ARG DATABASE_URL
ARG BASE_URL
ARG PRECONTENTIEUX_COURRIEL_EQUIPE
ARG SENTRY_DSN

RUN composer install --no-ansi --no-dev --no-progress --optimize-autoloader

RUN yarn install --frozen-lockfile && yarn build && yarn vite build

WORKDIR /app/public

CMD ["frankenphp", "php-server", "-a", "--worker", "./index.php"]
