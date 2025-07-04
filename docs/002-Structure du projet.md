# Structure du projet

## Backend: Symfony

Le projet a été initié en Symfony. On utilise donc Doctrine pour s'interfacer avec la base de données PostgreSQL.

On dispose des migrations Doctrine, ainsi que des _fixtures_ de données et du [`doctrine-test-bundle`](https://github.com/dmaicher/doctrine-test-bundle) pour les tests.

Le projet a débuté avec API Platform, qui est actuellement utilisé uniquement pour le patch d'un dossier (au préalable
initié avec toutes les entités liées). On souhaite s'en affranchir à moyen terme.

Pour s'interfacer avec les assets du [frontend](#Frontend-Twig-et-React), eux même construits par Vite, on utlise le
[Pentatrion Vite Bundle](https://symfony-vite.pentatrion.com/).   

## Frontend: Twig et React

La plupart des pages ont donc été générées en Twig, côté backend.

De plus, nous migrons vers React, pour l'instant page par page.