import React,{useState,useEffect,useRef} from 'react';
import {default as RecapitulatifPersonneMorale} from '../PersonneMorale/Recapitulatif';
import {default as RecapitulatifPersonnePhysique} from '../PersonnePhysique/Recapitulatif';
import {default as RecapitulatifRepresentantLegal} from '../RepresentantLegal/Recapitulatif';
import {default as RecapitulatifServiceEnqueteur} from '../ServiceEnqueteur/Recapitulatif';
import {default as RecapitulatifReceveurAttestation} from '../ReceveurAttestation/Recapitulatif';
import {default as RecapitulatifDocument} from '../Document/Recapitulatif';
import { Br,Loading } from '../../utils/fundamental';
import { normalizeDate } from '../../utils/cast';
import { Button } from "@codegouvfr/react-dsfr/Button";
import {trans,GLOBAL_REVERT,CATEGORIE_DEMANDE_BRIS_PORTE_TITLE,
  ADRESSE_FIELD_LIGNE1_BRIS_PORTE_RECAP,BRIS_PORTE_FIELD_IS_PORTE_BLINDEE_SMALL,
  BRIS_PORTE_FIELD_DATE_OPERATION_PJ, ADRESSE_FIELD_LIGNE1_BRIS_PORTE,
  BRIS_PORTE_FIELD_IS_ERREUR_PORTE_SMALL,
  GLOBAL_YES, GLOBAL_NO,GLOBAL_UNKNOWN, BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
  BRIS_PORTE_FIELD_QUALITE_REMISE_ATTESTATION,BRIS_PORTE_PJ_SECTION,
  BRIS_PORTE_FIELD_QUALITE_REPRESENTANT
} from '../../../translator';

const SpecificRecapitulatif = ({brisPorte}) => {
  return (
    <>
      <h3>{trans(CATEGORIE_DEMANDE_BRIS_PORTE_TITLE)}</h3>
      <div className="fr-mb-2w">
        <label>{trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)}</label> : <strong>{normalizeDate(brisPorte.dateOperationPJ)}</strong>
      </div>
      <label>{trans(ADRESSE_FIELD_LIGNE1_BRIS_PORTE_RECAP)} :</label>
      <dl className="fr-mb-2w">
        <address>
          <dd>{brisPorte.adresse.ligne1}</dd>
          <dd>{brisPorte.adresse.codePostal} {brisPorte.adresse.localite}</dd>
        </address>
      </dl>
        <dl className="fr-mb-2w">
          <dd>{trans(BRIS_PORTE_FIELD_IS_PORTE_BLINDEE_SMALL)} : <strong>{brisPorte.isPorteBlindee?trans(GLOBAL_YES):trans(GLOBAL_NO)}</strong></dd>
          <dd>{trans(BRIS_PORTE_FIELD_IS_ERREUR_PORTE_SMALL)} : <strong>{brisPorte.isErreurPorte?trans(GLOBAL_YES):trans(GLOBAL_NO)}</strong></dd>
        </dl>
        <div className="fr-mb-2w">
          <label>{trans(BRIS_PORTE_FIELD_QUALITE_REPRESENTANT)} : <strong>{brisPorte.qualiteRequerant}</strong></label>
        </div>
        <RecapitulatifReceveurAttestation receveurAttestation={brisPorte.receveurAttestation} />
    </>
  );
}

const Recapitulatif = ({user,brisPorte,gotoFirstSection,gotoSecondSection,gotoThirdSection}) => {

  const personneMoraleUri = Routing.generate('_api_personne_morale_get',{id: user.personneMorale.id});
  const personnePhysiqueUri = Routing.generate('_api_personne_physique_get',{id: user.personnePhysique.id});
  const brisPorteUri = Routing.generate('_api_bris_porte_get',{id: brisPorte.id});
  const brisPorteUriOptimizedUri = Routing.generate('_api_bris_porte_get_optimized',{id: brisPorte.id});

  const userUri = Routing.generate('_api_user_get',{id:user.pId});
  const [loading,setLoading]=useState(false);
  const [blob,setBlob]=useState({});

  var locked = useRef(false);
  
  useEffect(() => {

    if(locked.current === true)
      return;
    locked.current = true;

    Promise
      .all([brisPorteUriOptimizedUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([_blob]) => {
        setBlob(_blob);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[]);

  return (
    <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
      <div className="fr-col-6">
        <section className="pr-form-section fr-p-4w">
        {loading && blob.user.isPersonneMorale &&
          <>
            <RecapitulatifPersonneMorale adresse={blob.user.adresse} personneMorale={blob.user.personneMorale}/>
            <RecapitulatifRepresentantLegal personnePhysique={blob.user.personnePhysique} />
            <Button
              onClick={gotoFirstSection}
              priority="secondary"
            >{trans(GLOBAL_REVERT)}</Button>
          </>
        }
        {loading && !blob.user.isPersonneMorale &&
          <>
            <RecapitulatifPersonnePhysique adresse={blob.user.adresse} personnePhysique={blob.user.personnePhysique} />
            <Button
              onClick={gotoFirstSection}
              priority="secondary"
            >{trans(GLOBAL_REVERT)}</Button>
          </>
        }
        {!loading &&
          <Loading />
        }
        </section>
      </div>
      <div className="fr-col-6">
        <section className="pr-form-section fr-p-4w">
        {loading &&
          <>
            <RecapitulatifServiceEnqueteur serviceEnqueteur={blob.brisPorte.serviceEnqueteur}/>
            <Button
              onClick={gotoSecondSection}
              priority="secondary"
            >{trans(GLOBAL_REVERT)}</Button>
          </>
        }
        {!loading &&
          <Loading />
        }
        </section>
      </div>
      <div className="fr-col-12">
        <section className="pr-form-section fr-p-4w">
        {loading &&
          <>
            <SpecificRecapitulatif brisPorte={blob.brisPorte} />
            <Button
              onClick={gotoSecondSection}
              priority="secondary"
            >{trans(GLOBAL_REVERT)}</Button>
          </>
        }
        {!loading &&
          <Loading />
        }
        </section>
      </div>
      <div className="fr-col-12">
        <section className="pr-form-section fr-p-4w">
        {loading &&
          <>
            <RecapitulatifDocument
              isPersonneMorale={blob.user.isPersonneMorale}
              personneMoraleLiasseDocumentaireUri={Routing.generate('_api_liasse_documentaire_get',{id:blob.user.personneMorale.liasseDocumentaire.id})}
              personnePhysiqueLiasseDocumentaireUri={Routing.generate('_api_liasse_documentaire_get',{id:blob.user.personnePhysique.liasseDocumentaire.id})}
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
        </section>
      </div>
    </div>
  );
}

export default Recapitulatif;
