import React,{useState,useEffect} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, BRIS_PORTE_FIELD_DATE_OPERATION_PJ,
    BRIS_PORTE_FIELD_IS_PORTE_BLINDEE, BRIS_PORTE_FIELD_IS_ERREUR_PORTE,
    BRIS_PORTE_FIELD_IDENTITE_PERSONNE_RECHERCHEE,
    BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
    BRIS_PORTE_FIELD_NOM_REMISE_ATTESTATION,
    BRIS_PORTE_FIELD_PRENOM_REMISE_ATTESTATION,
    GLOBAL_YES, GLOBAL_NO
} from '../../translator';
import { castDate } from '../utils/cast';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";

const BrisPorte = ({brisPorte}) => {
  console.log(brisPorte);
  const [loading, setLoading]=useState(false);
  const [dateOperationPJ, setDateOperationPJ]=useState(castDate(brisPorte.dateOperationPJ));
  const [isPorteBlindee, setIsPorteBlindee]=useState(brisPorte.isPorteBlindee);
  const [isErreurPorte, setIsErreurPorte]=useState(brisPorte.isErreurPorte);
  const [identitePersonneRecherchee, setIdentitePersonneRecherchee]=useState(brisPorte.identitePersonneRecherchee??'');
  const [nomRemiseAttestation, setNomRemiseAttestation]=useState(brisPorte.nomRemiseAttestation??'');
  const [prenomRemiseAttestation, setPrenomRemiseAttestation]=useState(brisPorte.prenomRemiseAttestation??'');

  useEffect(() => {
    if(!loading) {
      setLoading(true);
      return;
    }

    const url =Routing.generate('_api_bris_porte_patch',{id:brisPorte.id});
    const data = { dateOperationPJ: dateOperationPJ, isPorteBlindee: isPorteBlindee,
      isErreurPorte: isErreurPorte,identitePersonneRecherchee: identitePersonneRecherchee,
      nomRemiseAttestation: nomRemiseAttestation, prenomRemiseAttestation: prenomRemiseAttestation
    };

    fetch(url, {
      method: 'PATCH',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
    .then((response) => response.json())
    .then((data) => console.log('backup bp'))
    ;
  },[dateOperationPJ,isPorteBlindee,isErreurPorte,identitePersonneRecherchee,
    nomRemiseAttestation, prenomRemiseAttestation
  ]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Input
          label={trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)}
          nativeInputProps={{
            type: 'date',value: dateOperationPJ, onChange: ev=>setDateOperationPJ(ev.target.value)
          }}
        />
      </div>
      <div className="fr-col-12">
        <RadioButtons
          legend={trans(BRIS_PORTE_FIELD_IS_PORTE_BLINDEE)}
          orientation='horizontal'
          options={[
                {
                    label: trans(GLOBAL_YES),
                    nativeInputProps: {
                        checked: (isPorteBlindee === true),
                        onChange: ()=> setIsPorteBlindee(true)
                    }
                },
                {
                    label: trans(GLOBAL_NO),
                    nativeInputProps: {
                        checked: (isPorteBlindee !== true),
                        onChange: ()=> setIsPorteBlindee(false)
                    }
                },
            ]}
        />
      </div>
      <div className="fr-col-12">
        <RadioButtons
          legend={trans(BRIS_PORTE_FIELD_IS_ERREUR_PORTE)}
          orientation='horizontal'
          options={[
                {
                    label: trans(GLOBAL_YES),
                    nativeInputProps: {
                        checked: (isErreurPorte === true),
                        onChange: ()=> setIsErreurPorte(true)
                    }
                },
                {
                    label: trans(GLOBAL_NO),
                    nativeInputProps: {
                        checked: (isErreurPorte !== true),
                        onChange: ()=> setIsErreurPorte(false)
                    }
                },
            ]}
        />
      </div>
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
      <div className="fr-col-4">{trans(BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION)}</div>
      <div className="fr-col-3">
        <Input
          label={trans(BRIS_PORTE_FIELD_NOM_REMISE_ATTESTATION)}
          nativeInputProps={{
            value: nomRemiseAttestation,
            onChange: ev=>setNomRemiseAttestation(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-4">
        <Input
          label={trans(BRIS_PORTE_FIELD_PRENOM_REMISE_ATTESTATION)}
          nativeInputProps={{
            value: prenomRemiseAttestation,
            onChange: ev=>setPrenomRemiseAttestation(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
    </div>
  );
}

export default BrisPorte;
