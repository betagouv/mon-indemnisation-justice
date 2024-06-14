import React,{useState,useEffect} from 'react';
import {default as RecapitulatifPersonneMorale} from '../PersonneMorale/Recapitulatif';
import {default as RecapitulatifPersonnePhysique} from '../PersonnePhysique/Recapitulatif';
import {default as RecapitulatifRepresentantLegal} from '../RepresentantLegal/Recapitulatif';
import {default as RecapitulatifServiceEnqueteur} from '../ServiceEnqueteur/Recapitulatif';
import {default as RecapitulatifQualite} from '../QualiteRequerant/Recapitulatif';
import {default as RecapitulatifReceveurAttestation} from '../ReceveurAttestation/Recapitulatif';
import {default as RecapitulatifDocument} from '../Document/Recapitulatif';
import { Br,Loading } from '../../utils/fundamental';
import { normalizeDate } from '../../utils/cast';
import { Button } from "@codegouvfr/react-dsfr/Button";
import {trans,GLOBAL_REVERT,CATEGORIE_DEMANDE_BRIS_PORTE_TITLE,
  BRIS_PORTE_FIELD_DATE_OPERATION_PJ, ADRESSE_FIELD_LIGNE1_BRIS_PORTE,
  BRIS_PORTE_FIELD_IS_PORTE_BLINDEE,BRIS_PORTE_FIELD_IS_ERREUR_PORTE,
  GLOBAL_YES, GLOBAL_NO,BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
  BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION,BRIS_PORTE_PJ_SECTION
} from '../../../translator';

const Recapitulatif = ({user,brisPorte,gotoFirstSection,gotoSecondSection,gotoThirdSection}) => {

  const personneMoraleUri = Routing.generate('_api_personne_morale_get',{id: user.personneMorale.id});
  const personnePhysiqueUri = Routing.generate('_api_personne_physique_get',{id: user.personnePhysique.id});
  const brisPorteUri = Routing.generate('_api_bris_porte_get',{id: brisPorte.id});
  const userUri = Routing.generate('_api_user_get',{id:user.pId});
  const [loading,setLoading]=useState(false);
  const [lUser,setLUser]=useState({});
  const [updatedBrisPorte,setUpdatedBrisPorte]=useState(brisPorte);
  useEffect(() => {
    if(true === loading)
      return;
    Promise
      .all([userUri, brisPorteUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([data,_brisPorte]) => {
        setUpdatedBrisPorte(_brisPorte);
        setLUser(data);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-6">
      {loading && lUser && lUser.isPersonneMorale &&
        <div className="fr-grid-row">
          <div className="fr-col-12">
            <RecapitulatifPersonneMorale adresseUri={lUser.adresse} uri={lUser.personneMorale} />
            <Br space={1}/>
            <RecapitulatifRepresentantLegal uri={lUser.personnePhysique} />
          </div>
        </div>
      }
      {loading && lUser && !lUser.isPersonneMorale &&
        <div className="fr-grid-row">
          <div className="fr-col-12">
            <RecapitulatifPersonnePhysique adresseUri={lUser.adresse} uri={lUser.personnePhysique} />
            <Br space={1}/>
          </div>
        </div>
      }
      {!loading &&
        <Loading />
      }
      {loading &&
        <Button
          onClick={gotoFirstSection}
          priority="secondary"
        >{trans(GLOBAL_REVERT)}</Button>
      }
      </div>
      <div className="fr-col-6">
      {loading &&
        <>
          <RecapitulatifServiceEnqueteur uri={updatedBrisPorte.serviceEnqueteur}/>
          <Br space={2}/>
          <Button
            onClick={gotoSecondSection}
            priority="secondary"
          >{trans(GLOBAL_REVERT)}</Button>
        </>
      }
      {!loading &&
        <Loading />
      }
      </div>
      <div className="fr-col-12">
      {loading &&
        <>
          <h2>{trans(CATEGORIE_DEMANDE_BRIS_PORTE_TITLE)}</h2>
          {trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)} : <b>{normalizeDate(updatedBrisPorte.dateOperationPJ)}</b>
          <Br space={2}/>
          {trans(ADRESSE_FIELD_LIGNE1_BRIS_PORTE)} :
          <Br/>
          <address>
            <b>
              {updatedBrisPorte.adresse.ligne1}
              <Br/>
              {updatedBrisPorte.adresse.codePostal} {updatedBrisPorte.adresse.localite}
            </b>
          </address>
          <Br/>
          {trans(BRIS_PORTE_FIELD_IS_PORTE_BLINDEE)} : <b>{updatedBrisPorte.isPorteBlindee?trans(GLOBAL_YES):trans(GLOBAL_NO)}</b>
          <Br/>
          {trans(BRIS_PORTE_FIELD_IS_ERREUR_PORTE)} : <b>{updatedBrisPorte.isErreurPorte?trans(GLOBAL_YES):trans(GLOBAL_NO)}</b>
          <Br space={2}/>
          <RecapitulatifQualite uri={updatedBrisPorte.qualiteRequerant} precision={updatedBrisPorte.precisionRequerant}/>
          <Br space={2}/>
          <RecapitulatifReceveurAttestation receveurAttestation={updatedBrisPorte.receveurAttestation} />
          <Br space={2}/>
          <Button
            onClick={gotoSecondSection}
            priority="secondary"
          >{trans(GLOBAL_REVERT)}</Button>
        </>
      }
      {!loading &&
        <Loading />
      }
      </div>
      <div className="fr-col-12">
      {loading &&
        <>
          <h2>{trans(BRIS_PORTE_PJ_SECTION)}</h2>
          <RecapitulatifDocument
            isPersonneMorale={lUser.isPersonneMorale}
            personneMoraleUri={personneMoraleUri}
            personnePhysiqueUri={personnePhysiqueUri}
            prejudiceUri={brisPorteUri}
          />
          <Button
            onClick={gotoThirdSection}
            priority="secondary"
          >{trans(GLOBAL_REVERT)}</Button>
        </>
      }
      {!loading &&
        <Loading />
      }
      </div>
    </div>
  );
}

export default Recapitulatif;
