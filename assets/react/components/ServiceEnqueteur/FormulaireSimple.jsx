import React,{useState,useEffect} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, BRIS_PORTE_SERVICE_ENQUETEUR_SECTION,
  SERVICE_ENQUETEUR_FIELD_NOM, GLOBAL_WAITING,
  SERVICE_ENQUETEUR_FIELD_TELEPHONE, SERVICE_ENQUETEUR_FIELD_EMAIL,
  SERVICE_ENQUETEUR_FIELD_NUMERO_PV, SERVICE_ENQUETEUR_FIELD_JURIDICTION,
  SERVICE_ENQUETEUR_FIELD_MAGISTRAT, SERVICE_ENQUETEUR_FIELD_NUMERO_PARQUET
} from '../../../translator';
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

  useEffect(() => {

    if(false===loading)
      return;

    const controller=new AbortController();
    const signal = controller.signal;

    const url =Routing.generate('_api_service_enqueteur_patch',{id:castNumber(serviceEnqueteurIri)});

    const data = {
      numeroPV: numeroPV, numeroParquet: numeroParquet, telephone: telephone,
      courriel: courriel, juridiction: juridiction, magistrat: magistrat,
      nom: nom
    };

    fetch(url, {
      signal,
      method: 'PATCH',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
      .then((response) => response.json())
      .then((data) => console.log('backup sj'))
      .catch(() => {})
    ;
    
    return () => controller.abort();
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
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h5>{trans(BRIS_PORTE_SERVICE_ENQUETEUR_SECTION)}</h5>
      </div>
      {loading &&
      <>
        <div className="fr-col-12">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_NOM)}
            nativeInputProps={{value: nom, onChange: ev=>setNom(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6 fr-pr-md-1w">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_TELEPHONE)}
            nativeInputProps={{value: telephone, onChange: ev=>setTelephone(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_EMAIL)}
            nativeInputProps={{value: courriel, onChange: ev=>setCourriel(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6 fr-pr-md-1w">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_NUMERO_PV)}
            nativeInputProps={{value: numeroPV, onChange: ev=>setNumeroPV(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_JURIDICTION)}
            nativeInputProps={{value: juridiction, onChange: ev=>setJuridiction(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6 fr-pr-md-1w">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_MAGISTRAT)}
            nativeInputProps={{value: magistrat, onChange: ev=>setMagistrat(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(SERVICE_ENQUETEUR_FIELD_NUMERO_PARQUET)}
            nativeInputProps={{value: numeroParquet, onChange: ev=>setNumeroParquet(ev.target.value)}}
          />
        </div>
      </>
      }
      {!loading &&
      <div className="fr-col-12">
        <h5>{trans(GLOBAL_WAITING)}</h5>
      </div>
      }
    </div>
  );
}

export default FormulaireSimple
