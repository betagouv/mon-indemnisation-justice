import React from 'react';
import { Br } from '../../utils/fundamental';

const Recapitulatif = ({personnePhysique}) => {
  return (
    <>
      <p className="fr-mb-5w">
        <label>Représenté.e par</label>
        <Br/>
        <strong>{personnePhysique.civilite} {personnePhysique.prenom1} {personnePhysique.nom}</strong>
      </p>
    </>
  );
}

export default Recapitulatif;
