import React from 'react';
const Recapitulatif = ({receveurAttestation}) => {

  return (
    <dl className="fr-mb-2w">
      <dd>Attestation remise à : <strong>{receveurAttestation.civilite} {receveurAttestation.prenom1} {receveurAttestation.nom}</strong></dd>
      <dd>En qualité de <strong>{receveurAttestation.qualite}</strong></dd>
    </dl>
  );
}

export default Recapitulatif;
