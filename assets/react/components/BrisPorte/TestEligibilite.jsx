import React,{useState,useEffect} from 'react';
import {trans,
  BRIS_PORTE_TEST_ELIGIBILITE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ERROR_CONTINUE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_SUCCESS_CONTINUE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_CHAPO,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_CHAPO,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_DESCRIPTION,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_TITLE,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_CHAPO,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_DESCRIPTION,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_TITLE,
  BRIS_PORTE_FIELD_DATE_OPERATION_PJ,
  BRIS_PORTE_FIELD_IS_ERREUR_PORTE,
  BRIS_PORTE_FIELD_NUMERO_PARQUET,
  BRIS_PORTE_FIELD_NUMERO_PV,
  GLOBAL_AND_OR,
  GLOBAL_NO,
  GLOBAL_YES,
  GLOBAL_BTN_STOP,
  GLOBAL_FIELD_REQUIRED
} from '../../../translator';
import parse from 'html-react-parser';
import Submit from '../Submit';
import Hidden from '../Hidden';
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { Alert } from "@codegouvfr/react-dsfr/Alert";

const modal = createModal({
    id: "foo-modal",
    isOpenedByDefault: false
});

const TestEligibilite = function() {

  const isOpen = useIsModalOpen(modal);
  const [dateOperationPJ, setDateOperationPJ]=useState("");
  const [isErreurPorte, setIsErreurPorte]=useState(false);
  const [numeroPV, setNumeroPV]=useState("");
  const [numeroParquet, setNumeroParquet]=useState("");
  const [panel, setPanel]=useState("common");
  const [buttons, setButtons]=useState([
    {
      onClick: () => handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet,isErreurPorte),
      doClosesModal: false,
      children: trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_BTN)
    }
  ]);

  useEffect(() => setButtons([
    {
      onClick: () => handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet,isErreurPorte),
      doClosesModal: false,
      children: trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_BTN)
    }
  ]),[dateOperationPJ,numeroPV,numeroParquet,isErreurPorte]);

  const resetButtons = () => setButtons([{
    onClick: () => handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet,isErreurPorte),
    doClosesModal: false,
    children: trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_BTN)
  }]);

  function handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet) {
    event.preventDefault();
    event.stopPropagation();
    if(!dateOperationPJ) {
      alert(trans(GLOBAL_FIELD_REQUIRED)+trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ));
      return;
    }
    if(!numeroPV && !numeroParquet) {
      alert(trans(GLOBAL_FIELD_REQUIRED)+trans(BRIS_PORTE_FIELD_NUMERO_PV)+trans(GLOBAL_AND_OR)+trans(BRIS_PORTE_FIELD_NUMERO_PARQUET));
      return;
    }
    if(false === isErreurPorte) {
      setButtons([
        {
            type: "submit",
            priority: 'secondary',
            doClosesModal: false,
            children: trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ERROR_CONTINUE_BTN)
        },
        {
            onClick: () => handleStop(),
            children: trans(GLOBAL_BTN_STOP)
        }
      ]);
      setPanel("alert");
    }
    else {
      setButtons([
        {
            type: "submit",
            doClosesModal: false,
            children: trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_SUCCESS_CONTINUE_BTN)
        }
      ])
      setPanel("success");
    }
  }

  function handleStop() {
    modal.close();
    setPanel("common");
    resetButtons();
  }

  const handleContinue = (event) => {
    if(panel == "common")
      event.preventDefault();
  }

  return (
    <form onSubmit={handleContinue} name="form" action={Routing.generate("app_inscription")} method="POST">
      <Hidden name="dateOperationPJ" value={dateOperationPJ} />
      <Hidden name="type" value="BRI" />
      <Hidden name="numeroPV" value={numeroPV} />
      <Hidden name="numeroParquet" value={numeroParquet} />
      <Hidden name="isErreurPorte" value={isErreurPorte} />
      <section className="pr-eligibilite_conditions">
        <div className="fr-pt-8w">
          <h2>Quelles sont les conditions à remplir pour être indemnisé(e) ?</h2>
          <ul>
            <li>Vous ne devez pas être concerné(e) par l'opération de police judiciaire</li>
          </ul>
          <b>ET</b>
          <ul>
            <li>L'opération de police judiciaire doit avoir été réalisée par erreur à votre domicile
            </li>
          </ul>
        </div>
      </section>
      <section className="pr-eligibilite_action">
        <div className="fr-pb-8w fr-pt-2w">
          <Button onClick={() => {
            modal.open();
            setPanel("common");
            resetButtons();
            event.preventDefault();
          }}>
          {trans(BRIS_PORTE_TEST_ELIGIBILITE_BTN)}
          </Button>
        </div>
      </section>
      <modal.Component
        title={trans(BRIS_PORTE_TEST_ELIGIBILITE_BTN)}
        buttons={buttons}
      >
            {("common" == panel) &&

            <>
              <div className="fr-callout">
                <h2 className="fr-callout__title">Comment remplir ce formulaire ?</h2>
                <p className="fr-callout__text">{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_CHAPO)}</p>
              </div>
              
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-md-7 fr-col-12 fr-col-md-offset-5">
                  <Input
                    label={trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)}
                    nativeInputProps={{
                      type: 'date',
                      value: dateOperationPJ,
                      onChange: ev=>setDateOperationPJ(ev.target.value)
                    }}
                  />
                </div>
                <div className="fr-col-12">
                  <div className="fr-input-group">
                    <label className="fr-label" htmlFor="eligibilite-bris-de-porte_date-operation">Numéros de dossier
                      <span className="fr-hint-text">Vous devez compléter au moins un champ</span>
                    </label>
                  </div>
                  <div className="pr-eligibilite_form-group">
                    <div className="fr-p-2w">
                      <Input
                        label={trans(BRIS_PORTE_FIELD_NUMERO_PV)}
                        nativeInputProps={{
                          value: numeroPV,
                          onChange: ev=>setNumeroPV(ev.target.value),
                          maxLength: 255
                        }}
                      />
                      <Input
                        label={trans(BRIS_PORTE_FIELD_NUMERO_PARQUET)}
                        nativeInputProps={{
                          value: numeroParquet,
                          onChange: ev=>setNumeroParquet(ev.target.value),
                          maxLength: 255
                        }}
                      />
                    </div>
                  </div>
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
              </div>
            </>
            }
            {("alert" == panel) &&
            <>
              <Alert
                description={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_DESCRIPTION)}
                onClose={function noRefCheck(){}}
                severity="error"
                title={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_TITLE)}
              />
              <p className="fr-mt-2w">{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_CHAPO)}</p>
            </>
            }
            {("success" == panel) &&
            <>
              <Alert
                description={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_DESCRIPTION)}
                onClose={function noRefCheck(){}}
                severity="success"
                title={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_TITLE)}
              />
              <p className="fr-mt-2w">{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_CHAPO)}</p>
            </>
            }
      </modal.Component>
    </form>
  );
}

export default TestEligibilite;
