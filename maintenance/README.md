# Page de maintenance

En cas de mise en maintenance, le site peut être déployé sur [une application de type "statique"](https://www.clever.cloud/developers/doc/deploy/applications/static/),
afin de disposer d'une page statique informant les usagers que la plateforme n'est pas disponible.

La seule opération préliminaire consiste à installer les dépendances via `pnpm` :

```bash
pnpm install --frozen-lockfile
```

La configuration à appliquer :
* `APP_FOLDER` : doit pointe sur le sous dossier `maintenance`
* `CC_WEBROOT` : `maintenance/public`
* `CC_PRE_BUILD_HOOK` : installe les dépendances via `pnpm install --frozen-lockfile`