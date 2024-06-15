import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans,
  BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
  BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION,
  GLOBAL_UNKNOWN
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
        .map((u) => (u) ? fetch(u).then((response) => response.json()) : null)
      )
      .then(([_civilite,_qualite]) => {
        setCivilite(_civilite??{libelle: trans(GLOBAL_UNKNOWN)});
        setQualite(_qualite??{libelle: trans(GLOBAL_UNKNOWN)});
        setLoading(true);
      })
      .catch(() => {})
    ;
  })

  const pr = receveurAttestation.precision?" - "+receveurAttestation.precision:"";

  return (
    <>
    {loading &&
      <dl className="fr-mb-2w">
        <dd>{trans(BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION)} <strong>{civilite.libelle} {receveurAttestation.prenom1} {receveurAttestation.nom}</strong></dd>
        <dd>{trans(BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION)} <strong>{qualite.libelle} {pr}</strong></dd>
      </dl>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
