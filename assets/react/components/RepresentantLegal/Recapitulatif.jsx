import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, REPRESENTANT_LEGAL_SMALL_TITLE} from '../../../translator';

const Recapitulatif = ({personnePhysique}) => {
  return (
    <>
      <p className="fr-mb-5w">
        <label>{trans(REPRESENTANT_LEGAL_SMALL_TITLE)}</label>
        <Br/>
        <strong>{personnePhysique.civilite} {personnePhysique.prenom1} {personnePhysique.nom}</strong>
      </p>
    </>
  );
}

export default Recapitulatif;
