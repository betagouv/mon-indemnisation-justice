import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, BRIS_PORTE_SERVICE_ENQUETEUR_SECTION,
  BRIS_PORTE_FIELD_NUMERO_PV, SERVICE_ENQUETEUR_FIELD_JURIDICTION,
  BRIS_PORTE_FIELD_NUMERO_PARQUET,SERVICE_ENQUETEUR_FIELD_MAGISTRAT
} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({uri}) => {

  const [serviceEnqueteur,setServiceEnqueteur]=useState({});
  const [loading,setLoading]=useState(false);

  useEffect(() => {
    if(true===loading)
      return;
    fetch(uri)
      .then((response) => response.json())
      .then((data) => {
        setServiceEnqueteur(data);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[])

  return (
    <>
    {loading &&
      <>
        <h2>{trans(BRIS_PORTE_SERVICE_ENQUETEUR_SECTION)}</h2>
        <Br/>
        <b>{serviceEnqueteur.nom}</b>
        <Br/>
        {serviceEnqueteur.telephone}
        <Br/>
        {serviceEnqueteur.courriel}
        <Br space={2}/>
        {trans(BRIS_PORTE_FIELD_NUMERO_PV)} : <b>{serviceEnqueteur.numeroPV}</b>
        <Br/>
        {trans(SERVICE_ENQUETEUR_FIELD_JURIDICTION)} : <b>{serviceEnqueteur.juridiction}</b>
        <Br/>
        {trans(BRIS_PORTE_FIELD_NUMERO_PARQUET)} : <b>{serviceEnqueteur.numeroParquet}</b>
        <Br/>
        {trans(SERVICE_ENQUETEUR_FIELD_MAGISTRAT)} : <b>{serviceEnqueteur.magistrat}</b>
      </>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
