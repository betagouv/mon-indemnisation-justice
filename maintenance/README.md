# Page de maintenance

En cas de mise en maintenance, le site peut être déployé sur [une application de type "statique"](https://www.clever.cloud/developers/doc/deploy/applications/static/),
afin de disposer d'une page statique informant les usagers que la plateforme n'est pas disponible.

La configuration à appliquer :
* `CC_PRE_BUILD_HOOK` : installe les dépendances via ` DSFR_ACCEPT_LICENSE=1 npm install @gouvfr/dsfr@latest && cp -r ./node_modules/@gouvfr/dsfr/dist public/dsfr`
* `APP_FOLDER` : doit pointe sur le sous dossier `maintenance`
* `CC_WEBROOT` : `maintenance/public`
