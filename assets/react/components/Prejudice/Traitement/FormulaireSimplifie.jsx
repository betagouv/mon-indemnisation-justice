import React,{useRef,useState,useEffect} from 'react';
import { Br,Wysiwyg } from '../../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { castDecimal } from '../../../utils/cast';
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import {trans, PREJUDICE_FIELD_NOTE, PREJUDICE_FIELD_PROPOSITION_INDEMNISATION,
  PREJUDICE_ACTION_REJET,PREJUDICE_ACTION_VALIDE, GLOBAL_YES, GLOBAL_NO,
  PREJUDICE_FIELD_DOCUMENT_TO_SIGN,
  PREJUDICE_FIELD_DOCUMENT_TO_SIGN_HINTEXT
} from "../../../../translator";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

const modal = createModal({
    id: "foo-modal",
    isOpenedByDefault: false
});

const FormulaireSimplifie = ({prejudice,dimension}) => {

  const [propositionIndemnisation,setPropositionIndemnisation]=useState(prejudice.propositionIndemnisation ?? 0.);
  const [motivationProposition,setMotivationProposition]=useState(prejudice.motivationProposition);
  const [note,setNote]=useState(prejudice.note);
  const [loading,setLoading]=useState(false);
  const [modalTitle,setModalTitle]=useState("");
  const [modalType,setModalType]=useState("");
  const [callback,setCallback]=useState(() => () => {});
  const urlHomepage = Routing.generate('app_agent_redacteur_accueil');
  const TARE_HEIGHT = 300;
  $(document).ready(() => {
    $(".editor-class")
      .css("height",dimension.height-TARE_HEIGHT)
    ;
  });
  const handleRejet = () => {
    const url = Routing.generate('app_redacteur_update_statut_to_rejet',{id: prejudice.pid});
    setModalTitle(trans(PREJUDICE_ACTION_REJET));
    setModalType("error");
    setCallback(() => () => {
      fetch(url)
        .then((response) => response.json())
        .then((data) => window.location.href = urlHomepage)
        .catch(() => {})
      ;
    });
    modal.open();
  }
  const handleValide = () => {
    const url = Routing.generate('app_redacteur_update_statut_to_valide',{id: prejudice.pid});
    setModalTitle(trans(PREJUDICE_ACTION_VALIDE));
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

  var keyUpTimer = useRef(null);
  const KEY_UP_TIMER_DELAY = 1000;

  useEffect(() => setLoading(true),[]);
  useEffect(() => {
    if(false === loading)
      return;
    const url =Routing.generate('_api_prejudice_patch',{id:prejudice.pid});
    const data = {
      note: note,
      propositionIndemnisation: propositionIndemnisation,
      motivationProposition: motivationProposition
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

  },[note,propositionIndemnisation,motivationProposition]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Wysiwyg label={trans(PREJUDICE_FIELD_NOTE)} value={note} setValue={setNote}/>
      </div>
      <div className="fr-col-12">
        <Wysiwyg label={"Motivation de la proposition"} value={motivationProposition} setValue={setMotivationProposition}/>
      </div>
      <div className="fr-col-12">
        <Input
          label={trans(PREJUDICE_FIELD_PROPOSITION_INDEMNISATION)}
          iconId="fr-icon-money-euro-box-line"
          nativeInputProps={{
            value: propositionIndemnisation,
            onChange: ev => setPropositionIndemnisation(castDecimal(ev.target.value)),
            maxLength: 11
          }}
        />
      </div>
      <div className="fr-col-12">
        <label className="fr-label">{trans(PREJUDICE_FIELD_DOCUMENT_TO_SIGN)}</label>
        <ul>
          <li>
            <a className="fr-link" href={Routing.generate('app_decision_bri_previsionnel_print',{type:'VALIDE',id: prejudice.pid})} target="_blank">{"Prévisionnel de la proposition d'indemnisation"}</a>
          </li>
          <li>
            <a className="fr-link" href={Routing.generate('app_decision_bri_previsionnel_print',{type:'REJETE',id: prejudice.pid})} target="_blank">{"Prévisionnel du rejet"}</a>
          </li>
        </ul>
      </div>

      <div className="fr-col-12">
        <Br/>
      </div>
      <div className="fr-col-12">
        <ul className="fr-btns-group fr-btns-group--inline-sm">
          <li>
            <Button onClick={handleRejet}>{trans(PREJUDICE_ACTION_REJET)}</Button>
          </li>
          <li>
            <Button onClick={handleValide}>{trans(PREJUDICE_ACTION_VALIDE)}</Button>
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

export default FormulaireSimplifie;
