import React,{useState,useEffect,useRef} from 'react';
import {default as RecapitulatifPersonneMorale} from '../PersonneMorale/Recapitulatif';
import {default as RecapitulatifPersonnePhysique} from '../PersonnePhysique/Recapitulatif';
import {default as RecapitulatifRepresentantLegal} from '../RepresentantLegal/Recapitulatif';
import {default as RecapitulatifServiceEnqueteur} from '../ServiceEnqueteur/Recapitulatif';
import {default as RecapitulatifReceveurAttestation} from '../ReceveurAttestation/Recapitulatif';
import {default as RecapitulatifDocument} from '../Document/Recapitulatif';
import { Loading } from '../../utils/fundamental';
import { normalizeDate } from '../../utils/cast';
import { Button } from "@codegouvfr/react-dsfr/Button";

const SpecificRecapitulatif = ({brisPorte}) => {
  return (
    <>
      <h3>Bris de porte</h3>
      <div className="fr-mb-2w">
        <label>Date de l'opération de police judiciaire</label> : <strong>{normalizeDate(brisPorte.dateOperationPJ)}</strong>
      </div>
      <label>À l'adresse suivante :</label>
      <dl className="fr-mb-2w">
        <address>
          <dd>{brisPorte.adresse.ligne1}</dd>
          <dd>{brisPorte.adresse.codePostal} {brisPorte.adresse.localite}</dd>
        </address>
      </dl>
        <dl className="fr-mb-2w">
          <dd>Porte blindée : <strong>{brisPorte.isPorteBlindee? 'Oui' : 'Non'}</strong></dd>
        </dl>
        <div className="fr-mb-2w">
          <label>J'effectue ma demande en qualité de : <strong>{brisPorte.qualiteRequerant}</strong></label>
        </div>
        <RecapitulatifReceveurAttestation receveurAttestation={brisPorte.receveurAttestation} />
    </>
  );
}

const Recapitulatif = ({user,brisPorte,gotoFirstSection=null,gotoSecondSection=null,gotoThirdSection=null}) => {
  const brisPorteUri = Routing.generate('_api_bris_porte_get',{id: brisPorte.id});
  const brisPorteUriOptimizedUri = Routing.generate('_api_bris_porte_get_optimized',{id: brisPorte.id});

  const [loading,setLoading]=useState(false);
  const [blob,setBlob]=useState({user});

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
      <div className="fr-col-lg-6 fr-col-12">
        <section className="pr-form-section fr-p-4w">
        {loading && blob.user.isPersonneMorale &&
          <>
            <RecapitulatifPersonneMorale adresse={blob.user.adresse} personneMorale={blob.user.personneMorale}/>
            <RecapitulatifRepresentantLegal personnePhysique={blob.user.personnePhysique} />
            {gotoFirstSection &&
            <Button
              onClick={gotoFirstSection}
              priority="secondary"
            >Corriger la saisie</Button>
            }
          </>
        }
        {loading && !blob.user.isPersonneMorale &&
          <>
            <RecapitulatifPersonnePhysique adresse={blob.user.adresse} personnePhysique={blob.user.personnePhysique} />
            {gotoFirstSection &&
            <Button
              onClick={gotoFirstSection}
              priority="secondary"
            >Corriger la saisie</Button>
            }
          </>
        }
        {!loading &&
          <Loading />
        }
        </section>
      </div>
      <div className="fr-col-lg-6 fr-col-12">
        <section className="pr-form-section fr-p-4w">
        {loading &&
          <>
            <RecapitulatifServiceEnqueteur serviceEnqueteur={blob.brisPorte.serviceEnqueteur}/>
            {gotoSecondSection &&
            <Button
              onClick={gotoSecondSection}
              priority="secondary"
            >Corriger la saisie</Button>
            }
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
            {gotoSecondSection &&
            <Button
              onClick={gotoSecondSection}
              priority="secondary"
            >Corriger la saisie</Button>
            }
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
            {gotoThirdSection &&
            <Button
              onClick={gotoThirdSection}
              priority="secondary"
            >Corriger la saisie</Button>
            }
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
