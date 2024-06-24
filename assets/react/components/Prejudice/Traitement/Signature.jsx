import React,{useRef,useState,useEffect} from 'react';
import { Br } from '../../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";
import parse from 'html-react-parser';
import { Button } from "@codegouvfr/react-dsfr/Button";
import { castDecimal } from '../../../utils/cast';
import { Document } from '../../PieceJointe/PieceJointe';
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import {trans, PREJUDICE_FIELD_NOTE, PREJUDICE_FIELD_PROPOSITION_INDEMNISATION,
  PREJUDICE_ACTION_REJET,PREJUDICE_ACTION_VALIDE, GLOBAL_YES, GLOBAL_NO,
  PREJUDICE_FIELD_DOCUMENT_TO_SIGN, PREJUDICE_FIELD_DOCUMENT_TO_SIGN_HINTEXT,
  PREJUDICE_FIELD_DOCUMENT_SIGNED,PREJUDICE_FIELD_SUBMIT
} from "../../../../translator";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

const modal = createModal({
    id: "foo-modal",
    isOpenedByDefault: false
});

const Signature = ({prejudice,dimension}) => {

  const isOpen = useIsModalOpen(modal);
  const [modalTitle,setModalTitle]=useState("");
  const [modalType,setModalType]=useState("");
  const [callback,setCallback]=useState(() => () => {});
  const urlHomepage = Routing.generate('app_chef_precontentieux_homepage');
  const TARE_HEIGHT = 400;

  const handleTransmission = () => {
    const url = Routing.generate('app_redacteur_update_statut_to_sign',{id: prejudice.pid});
    setModalTitle(trans(PREJUDICE_FIELD_SUBMIT));
    setModalType("success");
    setCallback(() => () => {
      fetch(url)
        .then((response) => response.json())
        .then((data) => window.location.href = urlHomepage)
        .catch(() => {})
      ;
    });
    modal.open();
  }

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <div className="fr-input-group fr-input-group--disabled">
          <label className="fr-label">{trans(PREJUDICE_FIELD_NOTE)}</label>
          <div className="fr-input" style={{color: 'var(--text-disabled-grey)',
    boxShadow: 'inset 0 -2px 0 0 var(--border-disabled-gr)'}}>{parse(prejudice.note)}</div>
        </div>
      </div>
      <div className="fr-col-12">
        <Input
          disabled
          label={trans(PREJUDICE_FIELD_PROPOSITION_INDEMNISATION)}
          iconId="fr-icon-money-euro-box-line"
          nativeInputProps={{value: prejudice.propositionIndemnisation}}
        />
      </div>
      <div className="fr-col-12">
        <Br/>
      </div>
      <div className="fr-col-12">
        <label className="fr-label">{trans(PREJUDICE_FIELD_DOCUMENT_TO_SIGN)}</label>
        <ul>
          <li>
            <a className="fr-link" href={Routing.generate('app_decision_bri_print',{id: prejudice.pid})} target="_blank">{trans(PREJUDICE_FIELD_DOCUMENT_TO_SIGN_HINTEXT)}</a>
          </li>
        </ul>
      </div>
      <div className="fr-col-12">
        <Document
          liasseDocumentaireIri={prejudice.pLiasseDocumentaire}
          label={trans(PREJUDICE_FIELD_DOCUMENT_SIGNED)}
          type={"signature_decision"}
        />
        <Br/>
      </div>
      <div className="fr-col-12">
        <ul className="fr-btns-group fr-btns-group--inline-sm">
          <li>
            <Button onClick={handleTransmission}>{trans(PREJUDICE_FIELD_SUBMIT)}</Button>
          </li>
        </ul>
      </div>
      <modal.Component
        buttons={[
          {onClick: ()=> {},children: trans(GLOBAL_NO)},
          {children: trans(GLOBAL_YES),onClick: callback}
        ]}
      >
        <Alert
          severity={modalType}
          title={modalTitle}
        />
      </modal.Component>
    </div>
  );
}

export default Signature;
