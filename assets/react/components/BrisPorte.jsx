import React,{useState,useEffect,useRef} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, BRIS_PORTE_FIELD_DATE_OPERATION_PJ,
    BRIS_PORTE_FIELD_IS_PORTE_BLINDEE, BRIS_PORTE_FIELD_IS_ERREUR_PORTE,
    BRIS_PORTE_FIELD_IDENTITE_PERSONNE_RECHERCHEE,
    BRIS_PORTE_FIELD_IS_ERREUR_PORTE_HINT_TEXT,
    BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
    ADRESSE_FIELD_LIGNE1_BRIS_PORTE,
    BRIS_PORTE_EDIT_DETAILS_REMISE,
    BRIS_PORTE_FIELD_NOM_REMISE_ATTESTATION,
    BRIS_PORTE_FIELD_PRENOM_REMISE_ATTESTATION,
    BRIS_PORTE_EDIT_DETAILS_H5,
    GLOBAL_YES, GLOBAL_NO
} from '../../translator';
import Requerant from './Requerant';
import FormulaireReceveur from './PersonnePhysique/FormulaireReceveur';
import Adresse from './Adresse';
import { checkUrl,generateUrl, castDate, checkDate, checkString, formatUrl,castUrl,formatDate } from '../utils/cast';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import FormulaireSimple from './ServiceEnqueteur/FormulaireSimple';
const BrisPorte = ({brisPorte}) => {

  const [dateOperationPJ, setDateOperationPJ]=useState(castDate(brisPorte.dateOperationPJ??""));
  const [isPorteBlindee, setIsPorteBlindee]=useState(brisPorte.isPorteBlindee);
  const [isErreurPorte, setIsErreurPorte]=useState(brisPorte.isErreurPorte);
  const [identitePersonneRecherchee, setIdentitePersonneRecherchee]=useState(brisPorte.identitePersonneRecherchee??'');
  const [nomRemiseAttestation, setNomRemiseAttestation]=useState(brisPorte.nomRemiseAttestation??'');
  const [prenomRemiseAttestation, setPrenomRemiseAttestation]=useState(brisPorte.prenomRemiseAttestation??'');
  const [qualiteRequerant, setQualiteRequerant]=useState(brisPorte.qualiteRequerant??"");
  const [precisionRequerant, setPrecisionRequerant]=useState(brisPorte.precisionRequerant??"");

  const [recordActived, setRecordActived]=useState(false);
  const [loading, setLoading]=useState(false);

  var keyUpTimer = useRef(null);
  const KEY_UP_TIMER_DELAY = 1000;

  useEffect(() => {
    if(true===loading)
      return;
    setLoading(true);
  },[]);
  function mustBeRecorded() {
    const test =
    !checkDate(dateOperationPJ,brisPorte.dateOperationPJ)||
    (isPorteBlindee!==brisPorte.isPorteBlindee)||
    (isErreurPorte!==brisPorte.isErreurPorte)||
    !checkString(identitePersonneRecherchee,brisPorte.identitePersonneRecherchee)||
    !checkString(nomRemiseAttestation,brisPorte.nomRemiseAttestation)||
    !checkString(prenomRemiseAttestation,brisPorte.prenomRemiseAttestation)||
    !checkUrl(qualiteRequerant,brisPorte.qualiteRequerant?generateUrl("/api/qualite_requerants/",brisPorte.qualiteRequerant.code):null)||
    !checkString(precisionRequerant,brisPorte.precisionRequerant)||
    (true === recordActived)
    ;

    setRecordActived(test);
    return test;
  }

  useEffect(() => {
    if(false===loading)
      return;
    if(false === mustBeRecorded())
      return;

    const url =Routing.generate('_api_bris_porte_patch',{id:brisPorte.id});
    const data = { dateOperationPJ: formatDate(dateOperationPJ), isPorteBlindee: isPorteBlindee,
      isErreurPorte: isErreurPorte,identitePersonneRecherchee: identitePersonneRecherchee,
      nomRemiseAttestation: nomRemiseAttestation, prenomRemiseAttestation: prenomRemiseAttestation,
      qualiteRequerant: formatUrl(qualiteRequerant), precisionRequerant: precisionRequerant
    };

    clearTimeout(keyUpTimer.current);
    keyUpTimer.current = setTimeout(() => {
      fetch(url, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
        .then((response) => response.json())
        .then((data) => {})
        .catch(() => {})
      ;
    },KEY_UP_TIMER_DELAY);
  },[dateOperationPJ,isPorteBlindee,isErreurPorte,identitePersonneRecherchee,
    nomRemiseAttestation, prenomRemiseAttestation, qualiteRequerant,
    precisionRequerant
  ]);

  return (
    <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
      <div className="fr-col-12">
        <a name="bris-de-porte"></a>
        <section className="pr-form-section fr-p-4w">
          <h3>{trans(BRIS_PORTE_EDIT_DETAILS_H5)}</h3>
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-4">
              <Input
                label={trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)}
                nativeInputProps={{type: 'date',value: dateOperationPJ, onChange: ev=>setDateOperationPJ(ev.target.value)}}
              />
            </div>
            <div className="fr-col-offset-8"></div>
            <div className="fr-col-12">
              <Adresse
                adresse={brisPorte.adresse}
                optionalLigne1Texte={trans(ADRESSE_FIELD_LIGNE1_BRIS_PORTE)}
              />
            </div>
            <div className="fr-col-12">
              <RadioButtons
                legend={trans(BRIS_PORTE_FIELD_IS_PORTE_BLINDEE)}
                orientation='horizontal'
                options={[
                  {label: trans(GLOBAL_YES),nativeInputProps: {checked: (isPorteBlindee === true),onChange: ()=> setIsPorteBlindee(true)}},
                  {label: trans(GLOBAL_NO),nativeInputProps: {checked: (isPorteBlindee !== true),onChange: ()=> setIsPorteBlindee(false)}},
                ]}
              />
            </div>
            <div className="fr-col-12">
              <RadioButtons
                hintText={trans(BRIS_PORTE_FIELD_IS_ERREUR_PORTE_HINT_TEXT)}
                legend={trans(BRIS_PORTE_FIELD_IS_ERREUR_PORTE)}
                orientation='horizontal'
                options={[
                  {label: trans(GLOBAL_YES),nativeInputProps: {checked: (isErreurPorte === true),onChange: ()=> setIsErreurPorte(true)}},
                  {label: trans(GLOBAL_NO),nativeInputProps: {checked: (isErreurPorte !== true),onChange: ()=> setIsErreurPorte(false)}},
                ]}
              />
            </div>
            {!isErreurPorte &&
            <div className="fr-col-12">
              <Input
                label={trans(BRIS_PORTE_FIELD_IDENTITE_PERSONNE_RECHERCHEE)}
                nativeInputProps={{
                  value: identitePersonneRecherchee,
                  onChange: ev=>setIdentitePersonneRecherchee(ev.target.value),
                  maxLength: 255
                }}
              />
            </div>
            }
            <div className="fr-col-12">
              <Requerant
                qualiteRequerant={qualiteRequerant}
                setQualiteRequerant={setQualiteRequerant}
                precisionRequerant={precisionRequerant}
                setPrecisionRequerant={setPrecisionRequerant}
              />
            </div>
          </div>
        </section>
      </div>
      <div className="fr-col-12">
        <section className="pr-form-section fr-p-4w">
          <FormulaireReceveur personnePhysique={brisPorte.receveurAttestation}/>
        </section>
      </div>
      <a name="service-enqueteur"></a>
      <div className="fr-col-12">
        <section className="pr-form-section fr-p-4w">
          <FormulaireSimple serviceEnqueteurIri={brisPorte.serviceEnqueteur}/>
        </section>
      </div>
    </div>
  );
}

export default BrisPorte;