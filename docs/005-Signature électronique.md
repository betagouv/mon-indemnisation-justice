# Signature électronique ✍️

## Signature électronique requérant

### En production

Nous utilisons **LegalySpace** comme prestataire de signature électronique, via son API B2B e-Sign ([spécification
OpenAPI](https://esign-api.legalyspace.com/v1/openapi)).

L'implémentation LegalySpace est activée sur la présence d'une variable d'environnement `LEGALYSPACE_API_KEY`renseignée.

En pré-production, les courriels sont réellement envoyés (utiliser ses propres adresses) mais pas les SMS : le code à
saisir est toujours `123456`.

### En test

Sur l'environnement de test, pour éviter de dépendre d'un service externe susceptible de bloquer la CI, nous utilisons un
prestataire local via [Documenso](https://github.com/documenso/documenso) (service `documenso` de `compose.yaml`), qui
permet les mêmes fonctionnalités que LegalySpace : téléversement du PDF à signer par API et notification par webhook.

Au premier démarrage, le conteneur génère un certificat auto-signé de scellement des documents dans
`.docker/documenso/certificat`. Il faut ensuite, depuis l'interface sur http://localhost:8082 :

1. créer un compte (les courriels de confirmation arrivent dans Mailpit, http://localhost:8025) ;
2. créer un jeton d'API (Paramètres de l'équipe > Jetons d'API) et le reporter dans `DOCUMENSO_API_KEY` ;
3. créer un webhook vers `http://symfony/webhooks/signature/evenement` pour l'évènement `DOCUMENT_COMPLETED`, avec pour
   secret la valeur de `DOCUMENSO_WEBHOOK_SECRET`.

À noter que si LegalySpace n'est pas configuré sur l'environnement de développement, c'est Documenso qui sera attribué.

