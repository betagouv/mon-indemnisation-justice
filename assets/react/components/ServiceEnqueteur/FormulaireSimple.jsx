import React,{useState,useEffect,useRef} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";

import { castNumber } from '../../utils/cast';

const FormulaireSimple = ({serviceEnqueteurIri}) => {

  const [loading,setLoading]=useState(false);
  const [numeroPV,setNumeroPV]=useState("");
  const [numeroParquet,setNumeroParquet]=useState("");
  const [nom,setNom]=useState("");
  const [telephone,setTelephone]=useState("");
  const [courriel,setCourriel]=useState("");
  const [juridiction,setJuridiction]=useState("");
  const [magistrat,setMagistrat]=useState("");

  var keyUpTimer = useRef(null);
  const KEY_UP_TIMER_DELAY = 1000;

  useEffect(() => {

    if(false===loading)
      return;

    const url =Routing.generate('_api_service_enqueteur_patch',{id:castNumber(serviceEnqueteurIri)});
    const data = {
      numeroPV: numeroPV, numeroParquet: numeroParquet, telephone: telephone,
      courriel: courriel, juridiction: juridiction, magistrat: magistrat,
      nom: nom
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
        .then((data) => { })
        .catch(() => {})
      ;
    },KEY_UP_TIMER_DELAY);
  },[numeroPV,numeroParquet,juridiction,magistrat,telephone,courriel,nom])
  useEffect(() => {
    if(true===loading)
      return;
    fetch(serviceEnqueteurIri)
      .then((response) => response.json())
      .then((data) => {
        setNumeroPV(data.numeroPV??"");
        setNom(data.nom??"");
        setTelephone(data.telephone??"");
        setCourriel(data.courriel??"");
        setJuridiction(data.juridiction??"");
        setMagistrat(data.magistrat??"");
        setNumeroParquet(data.numeroParquet??"");
        setLoading(true);
      });
  },[]);

  return (
    <>
      <h3>Service enquêteur</h3>
      <div className="fr-grid-row fr-grid-row--gutters">
        {loading &&
        <>
          <div className="fr-col-12">
            <Input
              label="Nom du service"
              nativeInputProps={{value: nom, onChange: ev=>setNom(ev.target.value)}}
            />
          </div>
          <div className="fr-col-lg-6 fr-col-12">
            <Input
              label="Téléphone"
              nativeInputProps={{value: telephone, onChange: ev=>setTelephone(ev.target.value)}}
            />
          </div>
          <div className="fr-col-lg-6 fr-col-12">
            <Input
              label="Courriel"
              nativeInputProps={{value: courriel, onChange: ev=>setCourriel(ev.target.value)}}
            />
          </div>
          <div className="fr-col-lg-6 fr-col-12">
            <Input
              label="Numéro de procès-verbal"
              nativeInputProps={{value: numeroPV, onChange: ev=>setNumeroPV(ev.target.value)}}
            />
          </div>
          <div className="fr-col-lg-6 fr-col-12">
            <Input
              label="Juridiction"
              nativeInputProps={{value: juridiction, onChange: ev=>setJuridiction(ev.target.value)}}
            />
          </div>
          <div className="fr-col-lg-6 fr-col-12">
            <Input
              label="Nom du magistrat"
              nativeInputProps={{value: magistrat, onChange: ev=>setMagistrat(ev.target.value)}}
            />
          </div>
          <div className="fr-col-lg-6 fr-col-12">
            <Input
              label="Numéro de parquet ou d'instruction"
              nativeInputProps={{value: numeroParquet, onChange: ev=>setNumeroParquet(ev.target.value)}}
            />
          </div>
        </>
        }
        {!loading &&
        <div className="fr-col-12">
          <h5>Chargement en cours ...</h5>
        </div>
        }
      </div>
    </>
  );
}

export default FormulaireSimple
