FROM nginx:alpine

# Update & install core packages
RUN apk update
RUN apk add bash git wget vim curl procps linux-headers

# Files
RUN echo "alias ll='ls -l'" > /root/.bashrc
RUN echo "alias la='ls -la'" >> /root/.bashrc
RUN echo "PS1='\[\033[01;35m\]\h \[\033[01;34m\]\w $\[\033[00m\] '" >> /root/.bashrc
RUN echo "export LANG='fr_FR.UTF8'" >> /root/.bashrc

COPY .docker/nginx/ssl/* /etc/nginx/certs/
COPY .docker/nginx/conf/*.conf /etc/nginx/conf.d/

WORKDIR /app
