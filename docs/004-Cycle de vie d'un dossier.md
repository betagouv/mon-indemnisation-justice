# Cycle de vie d'un dossiers

## Les différents états

Voici la liste des différents états par lesquels passe un dossier :
- **À finaliser** (`A_FINALISER`): le dossier a été initié par le requérant et est en cours de complétion
- **À instruire** (`A_INSTRUIRE`): le requérant a déposé son dossier. Il est désormais à instruire par le rédacteur qui lui est attribué
- **En cours d'instruction** (`EN_INSTRUCTION`): quand le rédacteur démarre l'instruction, il marque le dossier en cours d'instruction. Il a
alors accès aux coordonnées du requérant ainsi que la prise de notes sur le dossier
- **Clôturé** (`CLOTURE`): le dossier a été clôturé et ne sera pas traité. Ex: pas pertinent car ne concerne pas un
dossier de bris de porte ou est déjà traité en version papier 
- **Accepté - à signer** (`OK_A_SIGNER`): le rédacteur accepte la demande en rédigeant une première de la proposition d'indemnisation 
- **Accepté - en attente déclaration** (`OK_A_APPROUVER`): le validateur peut également éditer le PI et signe électroniquement
le document, lequel est transmis au requérant via son espace
- **Accepté - déclaration retournée** (`OK_A_VERIFIER`): le requérant a accepté la proposition en retournant la déclaration
d'acceptation depuis son espace. Le rédacteur la valide puis édite une première version de l'arrêté de paiement  
- **Accepté - arrêté à signer** (`OK_VERIFIE`): le validateur peut également éditer l'arrêté mais surtout le signe électroniquement 
- **Accepté - arrêté à transmettre à FIP3** (`OK_A_INDEMNISER`): l'agent de liaison récupère les documents nécessaires à
l'établissement du virement et les transmet à FIP3
- **Accepté - arrêté transmis à FIP3** (`OK_EN_ATTENTE_PAIEMENT`): l'agent de liaison vérifie régulièrement auprès de FIP3
si le virement a été effectué
- **Indemnisé par FIP3** (`OK_INDEMNISE`): l'indemnisation est virée sur le compte du requérant, le dossier est terminée  
- **Rejeté - à signer** (`KO_A_SIGNER`): le rédacteur propose un rejet et initie un courrier de décision en ce sens
- **Rejeté - envoyé** (`KO_REJETE`): le validateur peut éditer le courrier de décision et le signe pour qu'il soit envoyé
au requérant

## Diagramme d'états

```mermaid
---
title: Cycle de vie d'un dossiers
---
stateDiagram-v2
    direction TB

    classDef a_finaliser font-weight:bold,text-transform:uppercase,color:#6a6156,fill:#f3ede5
    classDef a_instruire font-weight:bold,text-transform:uppercase,color:#2f4077,fill:#f4f6fe
    classDef en_instruction font-weight:bold,text-transform:uppercase,color:#0063cb,fill:#e8edff
    classDef cloture font-weight:bold,text-transform:uppercase,color:#6a6156,fill:#f3ede5
    classDef ok_a_signer font-weight:bold,text-transform:uppercase,color:#66673d,fill:#fceeac
    classDef ok_a_approuver font-weight:bold,text-transform:uppercase,color:#447049,fill:#c9fcac
    classDef ok_a_verifier font-weight:bold,text-transform:uppercase,color:#447049,fill:#c9fcac
    classDef ok_verifie font-weight:bold,text-transform:uppercase,color:#297254,fill:#c3fad5
    classDef ok_a_indemniser font-weight:bold,text-transform:uppercase,color:#297254,fill:#c3fad5
    classDef ok_en_attente_paiement font-weight:bold,text-transform:uppercase,color:#297254,fill:#c3fad5
    classDef ok_indemnise font-weight:bold,text-transform:uppercase,color:#18753c,fill:#b8fec9
    classDef ko_a_signer font-weight:bold,text-transform:uppercase,color:#a94645,fill:#fee9e7
    classDef ko_rejete font-weight:bold,text-transform:uppercase,color:#ce0500,fill:#ffe9e9

    A_FINALISER: À finaliser
    A_INSTRUIRE: À instruire
    EN_INSTRUCTION: En instruction
    CLOTURE: Clôturé
    OK_A_SIGNER: Accepté - à signer
    OK_A_APPROUVER: Accepté - en attente déclaration
    OK_A_VERIFIER: Accepté - déclaration retournée
    OK_VERIFIE: Accepté - arrêté à signer
    OK_A_INDEMNISER: Accepté - arrêté à transmettre à FIP3
    OK_EN_ATTENTE_PAIEMENT: Accepté - arrêté transmis à FIP3
    OK_INDEMNISE: Indemnisé par FIP3
    KO_A_SIGNER: Rejeté - à signer
    KO_REJETE: Rejeté - envoyé

    [*] --> A_FINALISER :::a_finaliser : Requérant initie son dossier
    A_FINALISER --> A_INSTRUIRE ::: a_instruire : Requérant dépose son dossier
    A_INSTRUIRE --> EN_INSTRUCTION ::: en_instruction : Rédacteur démarre l'instruction
    A_INSTRUIRE --> CLOTURE ::: cloture : Rédacteur clos le dossier
    EN_INSTRUCTION --> CLOTURE ::: cloture : Rédacteur clos le dossier
    CLOTURE --> [*]
    EN_INSTRUCTION --> OK_A_SIGNER ::: ok_a_signer : Rédacteur propose l'indemnisation
    OK_A_SIGNER --> OK_A_APPROUVER ::: ok_a_approuver : Validateur signe et envoie la PI
    OK_A_APPROUVER --> OK_A_VERIFIER ::: ok_a_verifier : Requérant accepte l'indemnisation
    OK_A_VERIFIER --> OK_VERIFIE ::: ok_verifie : Rédacteur édite l'arrêté
    OK_VERIFIE --> OK_A_INDEMNISER ::: ok_a_indemniser : Validateur signe l'arrêté
    OK_A_INDEMNISER --> OK_EN_ATTENTE_PAIEMENT ::: ok_en_attente_paiement : Agent de liaison transmet les documents à FIP3
    OK_EN_ATTENTE_PAIEMENT --> OK_INDEMNISE ::: ok_indemnise : Agent de liaison marque comme indemnisé
    OK_INDEMNISE --> [*]
    EN_INSTRUCTION --> KO_A_SIGNER ::: ko_a_signer : Rédacteur propose le rejet
    KO_A_SIGNER --> KO_REJETE ::: ko_a_signer : Validateur signe le courrier de rejet
    KO_REJETE --> [*]
```
