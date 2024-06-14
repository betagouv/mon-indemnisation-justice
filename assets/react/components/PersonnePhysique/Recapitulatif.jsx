import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, USER_H5,
} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({uri,adresseUri}) => {

  const [loading,setLoading]=useState(false);
  const [personnePhysique,setPersonnePhysique]=useState({});
  const [civilite,setCivilite]=useState("");

  const updatePp = (data) => {
    setPersonnePhysique(data);
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
              updatePp(data);
            })
            .catch(() => {})
        }
        else
          updatePp(data);
      })
      .catch(() => {})
    ;
  },[]);

  return (
    <>
      <h3>{trans(USER_H5)}</h3>
      {loading &&
      <dl className="fr-mb-2w">
        <strong>{civilite} {personnePhysique.prenom1} {personnePhysique.nom}</strong>
        <RecapitulatifAdresse uri={adresseUri} />
      </dl>
      }
      {!loading &&
        <Loading />
      }
    </>
  );
}

export default Recapitulatif;
