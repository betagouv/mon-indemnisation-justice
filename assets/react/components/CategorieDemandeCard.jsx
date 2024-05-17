import React from 'react';
import { Card } from "@codegouvfr/react-dsfr/Card";
import { Badge } from "@codegouvfr/react-dsfr/Badge";
import { trans, CATEGORIE_DEMANDE_WAITING
} from '../../translator';

const CategorieDemandeCard = function({title,chapo,href='#',enabled=true,vertical=true}) {

  const tagTitle = (true==enabled) ? title : <div style={{color:"gray"}}>{title}</div>;
  return (
    <Card
      background
      border
      start={(false === enabled) && <Badge as="span" noIcon severity="warning">{trans(CATEGORIE_DEMANDE_WAITING)}</Badge>}
      desc={chapo}
      enlargeLink={enabled}
      imageAlt={title}
      horizontal={!vertical}
      imageUrl="https://www.systeme-de-design.gouv.fr/img/placeholder.16x9.png"
      linkProps={{ href: href }}
      size="medium"
      title={tagTitle}
      titleAs="h3"
    />
  );
}

export default CategorieDemandeCard;
