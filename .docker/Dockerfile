FROM nginx:alpine3.17

COPY nginx/nginx.conf /etc/nginx/
RUN mkdir /usr/share/nginx/html/anj-bise-mai-frontend
ADD anj-bise-mai-frontendd.tar /usr/share/nginx/html/anj-bise-mai-frontend

RUN chmod 777 /var/cache/nginx/ && \
    chmod 777 /var/run/

WORKDIR /usr/share/nginx/html/anj-bise-mai-frontend

EXPOSE 8080
