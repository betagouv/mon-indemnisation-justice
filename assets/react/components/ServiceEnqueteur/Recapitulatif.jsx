import React from 'react';
const Recapitulatif = ({serviceEnqueteur}) => {
  return (
    <>
      <h3>Service enquêteur</h3>
      <dl className="fr-mb-2w">
        <dt><strong>{serviceEnqueteur.nom}</strong></dt>
        <dd>{serviceEnqueteur.telephone}</dd>
        <dd>{serviceEnqueteur.courriel}</dd>
      </dl>
      <dl className="fr-mb-2w">
        <dd>Numéro de procès-verbal : <strong>{serviceEnqueteur.numeroPV}</strong></dd>
        <dd>Juridiction : <strong>{serviceEnqueteur.juridiction}</strong></dd>
        <dd>Numéro de parquet ou d'instruction : <strong>{serviceEnqueteur.numeroParquet}</strong></dd>
        <dd>Nom du magistrat : <strong>{serviceEnqueteur.magistrat}</strong></dd>
      </dl>
    </>
  );
}

export default Recapitulatif;
