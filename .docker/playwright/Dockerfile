FROM node:22

RUN npx -y playwright@1.52.0 install --with-deps

WORKDIR /app

CMD ["npx", "playwright", "test"]