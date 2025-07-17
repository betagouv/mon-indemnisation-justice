# Image PHP

### Comment la _builder_ ?

Via `buildx`:

```bash
docker buildx build --platform linux/amd64,linux/arm64 . -f .docker/php/Dockerfile -t pierrelemee/mij-frankenphp
```