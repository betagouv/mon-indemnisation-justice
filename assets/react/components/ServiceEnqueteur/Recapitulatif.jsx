import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, BRIS_PORTE_SERVICE_ENQUETEUR_SECTION,
  BRIS_PORTE_FIELD_NUMERO_PV, SERVICE_ENQUETEUR_FIELD_JURIDICTION,
  BRIS_PORTE_FIELD_NUMERO_PARQUET,SERVICE_ENQUETEUR_FIELD_MAGISTRAT
} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({serviceEnqueteur}) => {
  return (
    <>
      <h3>{trans(BRIS_PORTE_SERVICE_ENQUETEUR_SECTION)}</h3>
      <dl className="fr-mb-2w">
        <dt><strong>{serviceEnqueteur.nom}</strong></dt>
        <dd>{serviceEnqueteur.telephone}</dd>
        <dd>{serviceEnqueteur.courriel}</dd>
      </dl>
      <dl className="fr-mb-2w">
        <dd>{trans(BRIS_PORTE_FIELD_NUMERO_PV)} : <strong>{serviceEnqueteur.numeroPV}</strong></dd>
        <dd>{trans(SERVICE_ENQUETEUR_FIELD_JURIDICTION)} : <strong>{serviceEnqueteur.juridiction}</strong></dd>
        <dd>{trans(BRIS_PORTE_FIELD_NUMERO_PARQUET)} : <strong>{serviceEnqueteur.numeroParquet}</strong></dd>
        <dd>{trans(SERVICE_ENQUETEUR_FIELD_MAGISTRAT)} : <strong>{serviceEnqueteur.magistrat}</strong></dd>
      </dl>
    </>
  );
}

export default Recapitulatif;