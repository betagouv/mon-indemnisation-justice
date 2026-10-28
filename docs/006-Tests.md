# Tests

## Test fonctionnels:

Pour lancer un test fonctionnel, on utilise [Playwright](https://playwright.dev) et Firefox (pour le moment).

Pour pouvoir les lancer depuis le conteneur Docker:

```bash
# Lancer playwright via Docker:
docker compose up --profile playwright -d
```

Ensuite aller sur http://localhost:8080 et se laisser guider.

Pour l'heuren on se base sur le même environnement que celui des tests unitaires _backend_, à la différence que sur Symfony
on exploite le `dama/doctrine-test-bundle` qui permet de jouer chaque test au sein d'une transaction et de le rendre idempotent
et étanche. Ainsi, chaque test fonctionnel qui modifie la base de données (ex: faire transitioner un dossier vers un autre état),
ne peut être joué 2 fois d'affilé sans recharger les données de tests. Idem pour les tests backend qui reposent donc sur
des données incorrectes.

À faire pour stabiliser :
- [ ] Recharger les data fixtures avant chaque test via un appel à un endpoint dédié, valable uniquement sur l'env de test
- [ ] Dupliquer les données de test par navigateur, pour tester sur Chrome, Safari et Edge
- [ ] Appeler mailpit pour vérifier les courriels reçus
- [ ] Vérifier l'état des données en base après soumission des formulaires
