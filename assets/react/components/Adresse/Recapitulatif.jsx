import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, PERSONNE_MORALE_TITLE,
PERSONNE_MORALE_FIELD_SIREN_SIRET} from '../../../translator';

const Recapitulatif = ({adresse}) => {
  return (
    <>
      <dd>{adresse.ligne1}</dd>
      <dd>{adresse.codePostal} {adresse.localite}</dd>
    </>
  );
}

export default Recapitulatif;
