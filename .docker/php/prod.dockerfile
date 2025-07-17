FROM pierrelemee/mij-frankenphp

COPY . /app/

RUN composer install --no-ansi --no-dev --no-progress --no-scripts --optimize-autoloader

RUN yarn install --frozen-lockfile && yarn build && yarn vite build

CMD ["bash", "-c", "composer run-script auto-scripts && cd public && frankenphp php-server -a --worker ./index.php"]