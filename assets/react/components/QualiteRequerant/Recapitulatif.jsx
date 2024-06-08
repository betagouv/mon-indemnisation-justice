import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, BRIS_PORTE_FIELD_QUALITE_REPRESENTANT,
} from '../../../translator';
const Recapitulatif = ({uri,precision=""}) => {

  const [loading,setLoading]=useState(false);
  const [qualiteRequerant, setQualiteRequerant]=useState({});

  useEffect(() => {
    if(true===loading)
      return;
    fetch(uri)
      .then((response) => response.json())
      .then((data) => {
        setQualiteRequerant(data);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[])

  const pr = precision?" - "+precision:"";
  return (
    <>
    {loading &&
      <>
      {trans(BRIS_PORTE_FIELD_QUALITE_REPRESENTANT)} : <b>{qualiteRequerant.libelle} {pr}</b>
      </>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
