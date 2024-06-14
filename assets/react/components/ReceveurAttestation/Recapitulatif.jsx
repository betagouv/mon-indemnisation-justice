import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans,
  BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
  BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION
} from '../../../translator';
const Recapitulatif = ({receveurAttestation}) => {
  const [loading,setLoading]=useState(false);
  const [civilite, setCivilite]=useState({});
  const [qualite, setQualite]=useState({});

  useEffect(() => {
    if(true===loading)
      return;

    Promise
      .all([receveurAttestation.civilite,receveurAttestation.qualite]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([_civilite,_qualite]) => {
        setCivilite(_civilite);
        setQualite(_qualite);
        setLoading(true);
      })
      .catch(() => {})
    ;
  })

  const pr = receveurAttestation.precision?" - "+receveurAttestation.precision:"";

  return (
    <>
    {loading &&
      <>
        {trans(BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION)} <b>{civilite.libelle} {receveurAttestation.prenom1} {receveurAttestation.nom}</b>
        <Br/>
        {trans(BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION)} <b>{qualite.libelle} {pr}</b>
      </>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
