# Tests

## Test fonctionnels:

Pour lancer un test fonctionnel, on utilise [Playwright](https://playwright.dev) et Firefox (pour le moment).

Pour pouvoir les lancer depuis le conteneur Docker:

```bash
# Lancer playwright via Docker:
docker compose up --profile playwright -d
```

Ensuite aller sur http://localhost:8080 et se laisser guider.

À faire pour stabiliser :
- [ ] Recharger les data fixtures avant chaque test via un appel à un endpoint dédié, valable uniquement sur l'env de test
- [ ] Dupliquer les données de test par navigateur, pour tester sur Chrome, Safari et Edge
- [ ] Appeler mailpit pour vérifier les courriels reçus
- [ ] Vérifier l'état des données en base après soumission des formulaires
