import React from 'react';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';

const Recapitulatif = ({adresse,personneMorale}) => {
  return (
    <>
      <h3>Identité de la société</h3>
      <dl className="fr-mb-2w">
        <strong>{personneMorale.raisonSociale}</strong>
        <dd>SIREN / SIRET : {personneMorale.sirenSiret}</dd>
        <RecapitulatifAdresse adresse={adresse} />
      </dl>
    </>
  );
}

export default Recapitulatif;
