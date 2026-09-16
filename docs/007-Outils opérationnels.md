# Outils opérationnels


## Suivi des crashes & dysfonctionnements applicatifs

Nous utilisons le Sentry fourni par l'incubateur Beta.gouv. Ceci nous permet d'être notifié des erreurs aussi bien en
frontend qu'en backend.

Y figurent également des logs applicatifs qui nous permettent de suivre ce que remonte l'application au cours de son
fonctionnement.

## Tests fonctionnels sur les différents OS et appareils

Grâce à Sentry, nous pouvons être notifiés d'erreurs apparaissant côté requérant. Bien souvent, ceux-ci méritent d'être
reproduits sur des appareils similaires à ceux de nos usagers, avec les mêmes versions du système d'exploitation et du
navigateur.

C'est ce que nous permet LambdaTest (désormais TestMu AI) : jouer des sessions sur différents appareils, OS et navigateurs.
Il est également possible de jouer l'environnement de dev grâce à la [mise en place d'un tunnel SSH](https://www.testmuai.com/support/docs/sharing-lambda-tunnel/).

## Boîte de dialogue avec les utilisateurs

Nous avons également mis [Crisp](https://crisp.chat) en place, qui permet aux requérants identifiés sur leur espace.  