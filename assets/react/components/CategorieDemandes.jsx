import React from 'react';
import { trans, CATEGORIE_DEMANDE_TITLE, CATEGORIE_DEMANDE_CHAPO,
  CATEGORIE_DEMANDE_BRIS_PORTE_TITLE, CATEGORIE_DEMANDE_BRIS_PORTE_CHAPO,
  CATEGORIE_DEMANDE_PERTE_LOYER_TITLE, CATEGORIE_DEMANDE_PERTE_LOYER_CHAPO
} from '../../translator';
import CategorieDemandeCard from './CategorieDemandeCard';

const CategorieDemandes = function() {
  return (
    <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>{trans(CATEGORIE_DEMANDE_TITLE)}</h1>
          <p>{trans(CATEGORIE_DEMANDE_CHAPO)}</p>
        </div>
        <div className="fr-col-4">
          <CategorieDemandeCard
            title={trans(CATEGORIE_DEMANDE_BRIS_PORTE_TITLE)}
            chapo={trans(CATEGORIE_DEMANDE_BRIS_PORTE_CHAPO)}
            enabled={true}
          />
        </div>
        <div className="fr-col-4">
          <CategorieDemandeCard
            title={trans(CATEGORIE_DEMANDE_PERTE_LOYER_TITLE)}
            chapo={trans(CATEGORIE_DEMANDE_PERTE_LOYER_CHAPO)}
            enabled={false}
          />
        </div>
    </div>
  );
}

export default CategorieDemandes;
