FROM pierrelemee/mij-frankenphp

# Parce que l'application de dev est amenée à discuter directement avec franceconnect.gouv.test et proconnect.gouv.test,
# le conteneur doit disposer du cartificat racine qui a été utilisé pour générer les certificats HTTPS des sites sous
# nginx :
RUN apt-get update -y && \
    apt-get upgrade -y && \
    apt-get install -y openssl ca-certificates

# Convertir le certificat racine au format .crt
COPY .docker/nginx/ssl/rootCA.pem /usr/local/share/ca-certificates

RUN openssl x509 -inform PEM -in /usr/local/share/ca-certificates/rootCA.pem -out /usr/local/share/ca-certificates/rootCA.crt && \
    update-ca-certificates