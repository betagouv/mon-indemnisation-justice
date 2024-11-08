import React,{useState,useEffect,useRef} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import Requerant from './Requerant';
import FormulaireReceveur from './PersonnePhysique/FormulaireReceveur';
import Adresse from './Adresse';
import { checkUrl,generateUrl, castDate, checkDate, checkString, formatUrl,castUrl,formatDate } from '../utils/cast';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import FormulaireSimple from './ServiceEnqueteur/FormulaireSimple';
const BrisPorte = ({brisPorte}) => {

  const [dateOperationPJ, setDateOperationPJ]=useState(castDate(brisPorte.dateOperationPJ??""));
  const [isPorteBlindee, setIsPorteBlindee]=useState(brisPorte.isPorteBlindee);
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
    const data = {
      dateOperationPJ: formatDate(dateOperationPJ),
      isPorteBlindee: isPorteBlindee,
      identitePersonneRecherchee: identitePersonneRecherchee,
      nomRemiseAttestation: nomRemiseAttestation,
      prenomRemiseAttestation: prenomRemiseAttestation,
      qualiteRequerant: formatUrl(qualiteRequerant), precisionRequerant: precisionRequerant
    };

    clearTimeout(keyUpTimer.current);
    keyUpTimer.current = setTimeout(() => {
      fetch(url, {
        method: 'PATCH',
        redirect: 'error',
        headers: {'Content-Type': 'application/merge-patch+json'},
        body: JSON.stringify(data)
      })
        .then((response) => response.json())
        .then((data) => {})
        .catch(() => {})
      ;
    },KEY_UP_TIMER_DELAY);
  },[dateOperationPJ,isPorteBlindee,identitePersonneRecherchee,
    nomRemiseAttestation, prenomRemiseAttestation, qualiteRequerant,
    precisionRequerant
  ]);

  return (
    <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
      <div className="fr-col-12">
        <a name="bris-de-porte"></a>
        <section className="pr-form-section fr-p-4w">
          <h3>Informations sur le bris de porte</h3>
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-lg-4 fr-col-12">
              <Input
                label="Date de l'opération de police judiciaire"
                nativeInputProps={{type: 'date',value: dateOperationPJ, onChange: ev=>setDateOperationPJ(ev.target.value)}}
              />
            </div>
            <div className="fr-col-offset-8"></div>
            <div className="fr-col-12">
              <Adresse
                adresse={brisPorte.adresse}
                optionalLigne1Texte="Adresse complète du logement concerné par le bris de porte"
              />
            </div>
            <div className="fr-col-12">
              <RadioButtons
                legend="S'agit-il d'une porte blindée ?"
                orientation='horizontal'
                options={[
                  {label: "Oui",nativeInputProps: {checked: (isPorteBlindee === true),onChange: ()=> setIsPorteBlindee(true)}},
                  {label: "Non",nativeInputProps: {checked: (isPorteBlindee !== true),onChange: ()=> setIsPorteBlindee(false)}},
                ]}
              />
            </div>
            <div className="fr-col-12">
              <Input
                label="Si vous la connaissez, précisez l'identité de la personne recherchée"
                nativeInputProps={{
                  value: identitePersonneRecherchee,
                  onChange: ev=>setIdentitePersonneRecherchee(ev.target.value),
                  maxLength: 255
                }}
              />
            </div>
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
