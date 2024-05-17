import React from 'react';
import { Card } from "@codegouvfr/react-dsfr/Card";
import { Badge } from "@codegouvfr/react-dsfr/Badge";
import { trans, CATEGORIE_DEMANDE_WAITING
} from '../../translator';

const CategorieDemandeCard = function({title,chapo,enabled=true}) {
  return (
    <Card
      background
      border
      start={(false === enabled) && <Badge as="span" noIcon severity="warning">{trans(CATEGORIE_DEMANDE_WAITING)}</Badge>}
      desc={chapo}
      enlargeLink
      imageAlt={title}
      imageUrl="https://www.systeme-de-design.gouv.fr/img/placeholder.16x9.png"
      linkProps={{
        href: '#'
      }}
      size="medium"
      title={title}
      titleAs="h3"
    />
  );
}

export default CategorieDemandeCard;
