import React,{ useState } from 'react';
import { Br, Wysiwyg } from '../../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";

import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from '../../PieceJointe/PieceJointe';
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";

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
    setModalTitle("Transmettre la décision signée au requérant");
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
        <Wysiwyg
          label="Note sur le dossier (masquée aux requérants)"
          value={prejudice.note}
          setValue={()=>{}}
          readOnly={true}
        />
      </div>
      <div className="fr-col-12">
        <Input
          disabled
          label="Proposition d'indemnisation (€)"
          iconId="fr-icon-money-euro-box-line"
          nativeInputProps={{value: prejudice.propositionIndemnisation}}
        />
      </div>
      <div className="fr-col-12">
        <Br/>
      </div>
      <div className="fr-col-12">
        <label className="fr-label">Document à signer</label>
        <ul>
          <li>
            <a className="fr-link" href={Routing.generate('app_decision_bri_print',{id: prejudice.pid})} target="_blank">Cliquer ici pour télécharger la pièce à signer</a>
          </li>
        </ul>
      </div>
      <div className="fr-col-12">
        <Document
          liasseDocumentaireIri={prejudice.pLiasseDocumentaire}
          label="Document signé"
          type={"signature_decision"}
        />
        <Br/>
      </div>
      <div className="fr-col-12">
        <ul className="fr-btns-group fr-btns-group--inline-sm">
          <li>
            <Button onClick={handleTransmission}>Transmettre la décision signée au requérant</Button>
          </li>
        </ul>
      </div>
      <modal.Component
        buttons={[
          {onClick: ()=> {},children: "Non"},
          {children: "Oui",onClick: callback}
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
