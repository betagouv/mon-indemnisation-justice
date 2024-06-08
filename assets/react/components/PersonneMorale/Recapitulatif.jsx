import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, PERSONNE_MORALE_TITLE,
PERSONNE_MORALE_FIELD_SIREN_SIRET} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({uri,adresseUri}) => {

  useEffect(() => {
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
    <div className="fr-grid-row">
    {loading &&
      <>
        <div className="fr-col-12">
          <h2>{trans(PERSONNE_MORALE_TITLE)}</h2>
        </div>
        <div className="fr-col-12">
          <b>{personneMorale.raisonSociale}</b>
          <Br space={1}/>
          <div>
            {trans(PERSONNE_MORALE_FIELD_SIREN_SIRET)} : {personneMorale.sirenSiret}
            <RecapitulatifAdresse uri={adresseUri} />
          </div>
        </div>
      </>
    }
    {!loading &&
      <Loading />
    }
    </div>
  );
}

export default Recapitulatif;
