import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, PERSONNE_MORALE_TITLE,
PERSONNE_MORALE_FIELD_SIREN_SIRET} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({uri,adresseUri}) => {

  useEffect(() => {
    if(true===loading)
      return;
    fetch(uri)
      .then((response) => response.json())
      .then((data) => {
        setPersonneMorale(data);
        setLoading(true);
      })
  },[]);

  const [loading,setLoading]=useState(false);
  const [personneMorale,setPersonneMorale]=useState({});
  return (
    <>
      <h3>{trans(PERSONNE_MORALE_TITLE)}</h3>
      {loading &&
      <>
        <dl className="fr-mb-2w">
          <strong>{personneMorale.raisonSociale}</strong>
          <dd>{trans(PERSONNE_MORALE_FIELD_SIREN_SIRET)} : {personneMorale.sirenSiret}</dd>
          <RecapitulatifAdresse uri={adresseUri} />
        </dl>
      </>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
