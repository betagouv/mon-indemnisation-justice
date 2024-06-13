import React from 'react';
import { Card } from "@codegouvfr/react-dsfr/Card";
import { Badge } from "@codegouvfr/react-dsfr/Badge";
import { trans, CATEGORIE_DEMANDE_WAITING
} from '../../translator';

const CategorieDemandeCard = function({title,chapo,href='#',enabled=true,vertical=true}) {

  const tagTitle = (true==enabled) ? title : <div style={{color:"gray"}}>{title}</div>;
  return (
    <div className="fr-tile fr-enlarge-link" id="tile-6609">
      <div className="fr-tile__body">
        <div className="fr-tile__content">
          <h3 className="fr-tile__title">
            {enabled && <a href={href}>{tagTitle}</a>}
            {!enabled && <a>{tagTitle}</a>}
          </h3>
          <div className="fr-tile__start">
            {enabled &&
            <p className="fr-badge fr-badge--sm fr-badge--success fr-badge--no-icon">disponible</p>
            }
            {!enabled &&
            <Badge as="span" noIcon severity="warning">{trans(CATEGORIE_DEMANDE_WAITING)}</Badge>
            }
          </div>
          <p className="fr-tile__desc">{chapo}</p>
        </div>
      </div>
      <div className="fr-tile__header">
        <div className="fr-tile__pictogram">
        </div>
      </div>
    </div>
  );
}

export default CategorieDemandeCard;
