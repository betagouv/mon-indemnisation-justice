import React from 'react';
import { trans, CATEGORIE_DEMANDE_TITLE, CATEGORIE_DEMANDE_CHAPO,
  CATEGORIE_DEMANDE_BRIS_PORTE_TITLE, CATEGORIE_DEMANDE_BRIS_PORTE_CHAPO,
  CATEGORIE_DEMANDE_PERTE_LOYER_TITLE, CATEGORIE_DEMANDE_PERTE_LOYER_CHAPO,
  CATEGORIE_DEMANDE_ACCIDENT_TITLE, CATEGORIE_DEMANDE_ACCIDENT_CHAPO,
  CATEGORIE_DEMANDE_SINISTRE_TITLE, CATEGORIE_DEMANDE_SINISTRE_CHAPO,
  CATEGORIE_DEMANDE_DYSFONCTIONNEMENT_JUSTICE_TITLE, CATEGORIE_DEMANDE_DYSFONCTIONNEMENT_JUSTICE_CHAPO
} from '../../translator';
import CategorieDemandeCard from './CategorieDemandeCard';

const CategorieDemandes = function() {
  return (
    <section className="pr-requests">
      <div className="fr-px-4w fr-pt-4w">
        <h2>{trans(CATEGORIE_DEMANDE_TITLE)}</h2>
        <p>{trans(CATEGORIE_DEMANDE_CHAPO)}</p>
        <ul className="fr-grid-row fr-grid-row--gutters">
          <li className="fr-col-4">
            <CategorieDemandeCard
              title={trans(CATEGORIE_DEMANDE_BRIS_PORTE_TITLE)}
              chapo={trans(CATEGORIE_DEMANDE_BRIS_PORTE_CHAPO)}
              enabled={true}
              href={Routing.generate('app_bris_porte_test_eligibilite')}
            />
          </li>
          <li className="fr-col-4">
            <CategorieDemandeCard
              title={trans(CATEGORIE_DEMANDE_PERTE_LOYER_TITLE)}
              chapo={trans(CATEGORIE_DEMANDE_PERTE_LOYER_CHAPO)}
              enabled={false}
            />
          </li>
          <li className="fr-col-4">
            <CategorieDemandeCard
              title={trans(CATEGORIE_DEMANDE_DYSFONCTIONNEMENT_JUSTICE_TITLE)}
              chapo={trans(CATEGORIE_DEMANDE_DYSFONCTIONNEMENT_JUSTICE_CHAPO)}
              enabled={false}
            />
          </li>
          <li className="fr-col-6">
            <CategorieDemandeCard
              title={trans(CATEGORIE_DEMANDE_ACCIDENT_TITLE)}
              chapo={trans(CATEGORIE_DEMANDE_ACCIDENT_CHAPO)}
              enabled={false}
              vertical={false}
            />
          </li>
          <li className="fr-col-6">
            <CategorieDemandeCard
              title={trans(CATEGORIE_DEMANDE_SINISTRE_TITLE)}
              chapo={trans(CATEGORIE_DEMANDE_SINISTRE_CHAPO)}
              enabled={false}
              vertical={false}
            />
          </li>
        </ul>
      </div>
    </section>
  );
}

export default CategorieDemandes;
