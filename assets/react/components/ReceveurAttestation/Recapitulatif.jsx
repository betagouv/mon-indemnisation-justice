import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans,
  BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
  BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION,
  GLOBAL_UNKNOWN
} from '../../../translator';
const Recapitulatif = ({receveurAttestation}) => {

  return (
    <dl className="fr-mb-2w">
      <dd>{trans(BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION)} <strong>{receveurAttestation.civilite} {receveurAttestation.prenom1} {receveurAttestation.nom}</strong></dd>
      <dd>{trans(BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION)} <strong>{receveurAttestation.qualite}</strong></dd>
    </dl>
  );
}

export default Recapitulatif;
