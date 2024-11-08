import React from 'react';

import ReadOnlyInput from '../ReadOnlyInput';
import RequerantView from '../Requerant/View';
import { normalizeDate } from '../../utils/cast';

const BrisPorteView = ({brisPorte}) => {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <ReadOnlyInput
          label="Date de l'opération de police judiciaire"
          value={normalizeDate(brisPorte.dateOperationPJ)}
        />
      </div>
      <div className="fr-col-12">
        <ReadOnlyInput
          label="S'agit-il d'une porte blindée ?"
          value={brisPorte.isPorteBlindee ? "Oui" : "Non"}
        />
      </div>
      <div className="fr-col-12">
        <ReadOnlyInput
          label="Si NON, préciser l'identité de la personne recherchée"
          value={brisPorte.identitePersonneRecherchee}
        />
      </div>
      <div className="fr-col-4">Attestation remise à : </div>
      <div className="fr-col-3">
        <ReadOnlyInput
          label="NOM"
          value={brisPorte.nomRemiseAttestation}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-4">
        <ReadOnlyInput
          label="PRENOM"
          value={brisPorte.prenomRemiseAttestation}
        />
      </div>
      <div className="fr-col-12">
        <RequerantView
          qualiteRequerant={brisPorte.qualiteRequerant}
          precisionRequerant={brisPorte.precisionRequerant}
        />
      </div>
    </div>
  );
}

export default BrisPorteView;
