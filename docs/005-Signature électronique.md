# Signature électronique ✍️

## Signature électronique requérant

### En production

Nous utilisons Youtrust comme prestataire de signature électronique.

Pour les environnements de développement et de recette, l'application interagit avec [l'environnement "bac à sable"](https://developers.youtrust.com/docs/environments-new).
Une clef 🔐 d'API est requise, via `YOUSIGN_API_KEY`, pour commencer l'intégration.

Une fois la requête de signature initiée, on reçoit les mises à jour via une requête de webhook. Il faut donc au préalable
s'abonner :

```sh
curl --request POST \
     --url https://api.yousign.app/v3/webhooks \
     --header "Authorization: Bearer ${YOUTRUST_API_KEY}" \
     --header 'accept: application/json' \
     --header 'content-type: application/json' \
     --data @- <<EOF
{
  "endpoint": "https://mon-indemnisation.justice.gouv.fr/webhooks/signature/evenement",
  "sandbox": true,
  "subscribed_events": [
    "signature_request.done"
  ],
  "scopes": ["*"],
  "auto_retry": true,
  "enabled": true
}
EOF
```

La liste de tous les évènements est disponible [ici](https://developers.youtrust.com/docs/webhooks#-available-events).

L'intégration sur un poste de développement nécessitant d'être joignable par les serveurs de bac à sable de Youtrust, il est
utile d'ouvrir un tunnel vers un nom de domaine accessible sur le net. Un service `ngrok` est disponible en ce sens, à configurer
localement par chaque développeur.

### En test

Sur l'environnement de test, pour éviter de dépendre d'un service externe susceptible de bloquer la CI, nous utilisons un
prestataire local via [DocuSeal](https://www.docuseal.com/signing-api) qui permet les mêmes fonctionnalités que Youtrust.

À noter que si Youtrust n'est pas configuré sur l'environnement de développement, c'est DocuSeal qui sera attribué.