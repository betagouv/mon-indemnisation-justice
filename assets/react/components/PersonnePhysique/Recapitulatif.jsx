import React from 'react';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({personnePhysique,adresse}) => {
  return (
    <>
      <h3>Votre identité</h3>
      <dl className="fr-mb-2w">
        <strong>{personnePhysique.civilite} {personnePhysique.prenom1} {personnePhysique.nom}</strong>
        <RecapitulatifAdresse adresse={adresse} />
      </dl>
    </>
  );
}

export default Recapitulatif;
