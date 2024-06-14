import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, PERSONNE_MORALE_TITLE,
PERSONNE_MORALE_FIELD_SIREN_SIRET} from '../../../translator';

const Recapitulatif = ({uri}) => {
  const [loading,setLoading]=useState(false);
  const [adresse,setAdresse]=useState({});
  useEffect(() => {
    if(true===loading)
      return;
    fetch(uri)
      .then((response) => response.json())
      .then((data) => {
        setAdresse(data);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[]);
  return (
    <>
    {loading &&
      <>
        <dd>{adresse.ligne1}</dd>
        <dd>{adresse.codePostal} {adresse.localite}</dd>
      </>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
