import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, REPRESENTANT_LEGAL_SMALL_TITLE} from '../../../translator';

const Recapitulatif = ({uri}) => {

  const [loading,setLoading]=useState(false);
  const [representantLegal,setRepresentantLegal]=useState({});
  const [civilite,setCivilite]=useState("");
  const updateRl = (data) => {
    setRepresentantLegal(data);
    setLoading(true);
  }
  useEffect(() => {
    if(true===loading)
      return;
    fetch(uri)
      .then((response) => response.json())
      .then((data) => {
        if(data.civilite) {
          fetch(data.civilite)
            .then((response) => response.json())
            .then((sdata) => {
              setCivilite(sdata.libelleCourt);
              updateRl(data);
            })
            .catch(() => {})
        }
        else
          updateRl(data);
      })
      .catch(() => {})
    ;
  },[]);
  return (
    <>
    {loading &&
      <p className="fr-mb-5w">
        <label>{trans(REPRESENTANT_LEGAL_SMALL_TITLE)}</label>
        <Br/>
        <strong>{civilite} {representantLegal.prenom1} {representantLegal.nom}</strong>
      </p>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
