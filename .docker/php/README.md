# Image PHP

### Comment la _builder_ ?

Via `buildx`:

```bash
docker buildx build --platform linux/amd64,linux/arm64 . -f .docker/php/Dockerfile -t pierrelemee/mij-frankenphp --build-arg DATABASE_URL=<DATABASE_URL> --build-arg BASE_URL=<BASE_URL> --build-arg PRECONTENTIEUX_COURRIEL_EQUIPE=<PRECONTENTIEUX_COURRIEL_EQUIPE>
```