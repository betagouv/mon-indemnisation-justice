import React from 'react';

const Recapitulatif = ({adresse}) => {
  return (
    <>
      <dd>{adresse.ligne1}</dd>
      <dd>{adresse.codePostal} {adresse.localite}</dd>
    </>
  );
}

export default Recapitulatif;
