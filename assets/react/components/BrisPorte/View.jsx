import React,{useState,useEffect} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { trans, BRIS_PORTE_FIELD_DATE_OPERATION_PJ,
    BRIS_PORTE_FIELD_IS_PORTE_BLINDEE, BRIS_PORTE_FIELD_IS_ERREUR_PORTE,
    BRIS_PORTE_FIELD_IDENTITE_PERSONNE_RECHERCHEE,
    BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION,
    BRIS_PORTE_FIELD_NOM_REMISE_ATTESTATION,
    BRIS_PORTE_FIELD_PRENOM_REMISE_ATTESTATION,
    GLOBAL_YES, GLOBAL_NO
} from '../../../translator';
import ReadOnlyInput from '../ReadOnlyInput';
import RequerantView from '../Requerant/View';
import { normalizeDate, checkUrl, castDate, checkDate, checkString, formatUrl,castUrl,formatDate } from '../../utils/cast';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";

const BrisPorteView = ({brisPorte}) => {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <ReadOnlyInput
          label={trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)}
          value={normalizeDate(brisPorte.dateOperationPJ)}
        />
      </div>
      <div className="fr-col-12">
        <ReadOnlyInput
          label={trans(BRIS_PORTE_FIELD_IS_PORTE_BLINDEE)}
          value={brisPorte.isPorteBlindee ? trans(GLOBAL_YES) : trans(GLOBAL_NO)}
        />
      </div>
      <div className="fr-col-12">
        <ReadOnlyInput
          label={trans(BRIS_PORTE_FIELD_IS_ERREUR_PORTE)}
          value={brisPorte.isErreurPorte ? trans(GLOBAL_YES) : trans(GLOBAL_NO)}
        />
      </div>
      <div className="fr-col-12">
        <ReadOnlyInput
          label={trans(BRIS_PORTE_FIELD_IDENTITE_PERSONNE_RECHERCHEE)}
          value={brisPorte.identitePersonneRecherchee}
        />
      </div>
      <div className="fr-col-4">{trans(BRIS_PORTE_FIELD_PREFIX_REMISE_ATTESTATION)}</div>
      <div className="fr-col-3">
        <ReadOnlyInput
          label={trans(BRIS_PORTE_FIELD_NOM_REMISE_ATTESTATION)}
          value={brisPorte.nomRemiseAttestation}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-4">
        <ReadOnlyInput
          label={trans(BRIS_PORTE_FIELD_PRENOM_REMISE_ATTESTATION)}
          value={brisPorte.prenomRemiseAttestation}
        />
      </div>
      <div className="fr-col-12">
        <RequerantView
          qualiteRequerant={brisPorte.qualiteRequerant}
          precisionRequerant={brisPorte.precisionRequerant}
        />
      </div>
    </div>
  );
}

export default BrisPorteView;
