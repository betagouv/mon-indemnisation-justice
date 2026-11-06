FROM node:22-trixie-slim

# Generic utils
RUN apt-get update -y && \
    apt-get upgrade -y --fix-missing && \
    apt-get install -y vim wget ca-certificates

# Autorité de certification locale (mkcert) ayant signé les certificats servis par nginx
COPY ./.docker/nginx/ssl/rootCA.pem /usr/local/share/ca-certificates/mkcert-rootCA.crt
RUN update-ca-certificates
# Node n'utilise pas le magasin système : lui indiquer explicitement l'AC
ENV NODE_EXTRA_CA_CERTS=/usr/local/share/ca-certificates/mkcert-rootCA.crt

# Extra .bashrc config
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;33m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

WORKDIR /app

COPY ./end2end/package.json /app/
COPY ./end2end/playwright.config.ts /app/

RUN yarn install

RUN npx playwright install --with-deps

CMD ["npx", "playwright", "test"]