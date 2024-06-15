import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import {trans, PERSONNE_MORALE_TITLE,
PERSONNE_MORALE_FIELD_SIREN_SIRET} from '../../../translator';
import {default as RecapitulatifAdresse} from '../Adresse/Recapitulatif';
const Recapitulatif = ({adresse,personneMorale}) => {
  return (
    <>
      <h3>{trans(PERSONNE_MORALE_TITLE)}</h3>
      <dl className="fr-mb-2w">
        <strong>{personneMorale.raisonSociale}</strong>
        <dd>{trans(PERSONNE_MORALE_FIELD_SIREN_SIRET)} : {personneMorale.sirenSiret}</dd>
        <RecapitulatifAdresse adresse={adresse} />
      </dl>
    </>
  );
}

export default Recapitulatif;
