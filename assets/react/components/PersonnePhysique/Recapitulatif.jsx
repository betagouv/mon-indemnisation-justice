import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, USER_H5,
} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({personnePhysique,adresse}) => {
  return (
    <>
      <h3>{trans(USER_H5)}</h3>
      <dl className="fr-mb-2w">
        <strong>{personnePhysique.civilite} {personnePhysique.prenom1} {personnePhysique.nom}</strong>
        <RecapitulatifAdresse adresse={adresse} />
      </dl>
    </>
  );
}

export default Recapitulatif;
