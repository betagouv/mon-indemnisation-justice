import React,{useState,useEffect} from 'react';
import {trans, BRIS_PORTE_TEST_ELIGIBILITE_H2,
  BRIS_PORTE_TEST_ELIGIBILITE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ERROR_CONTINUE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_SUCCESS_CONTINUE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_DESCRIPTION,
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

  function handleTestEligibilite() {
    if(!dateOperationPJ) {
      alert(trans(GLOBAL_FIELD_REQUIRED)+trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ));
      return;
    }
    if(!numeroPV && !numeroParquet) {
      alert(trans(GLOBAL_FIELD_REQUIRED)+trans(BRIS_PORTE_FIELD_NUMERO_PV)+trans(GLOBAL_AND_OR)+trans(BRIS_PORTE_FIELD_NUMERO_PARQUET));
      return;
    }
    if(false === isErreurPorte)
      setPanel("alert");
    else
      setPanel("success");
  }

  function handleStop() {
    modal.close();
    setPanel("common");
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
      <section class="pr-eligibilite_conditions">
        <div class="fr-pt-8w">
          <h2>{trans(BRIS_PORTE_TEST_ELIGIBILITE_H2)}</h2>
          {parse(trans(BRIS_PORTE_TEST_ELIGIBILITE_DESCRIPTION))}
        </div>
      </section>
      <section className="pr-eligibilite_action">
        <div class="fr-pb-8w fr-pt-2w">
          <Button onClick={() => {modal.open()}}>
          {trans(BRIS_PORTE_TEST_ELIGIBILITE_BTN)}
          </Button>
        </div>
      </section>
      <modal.Component title={trans(BRIS_PORTE_TEST_ELIGIBILITE_BTN)}>
          <div className="fr-grid-row">
            {("common" == panel) &&
            <>
              <div className="fr-col-12">
              <p>{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_CHAPO)}</p>
              </div>
              <div className="fr-col-6">
                <Input
                  label={trans(BRIS_PORTE_FIELD_DATE_OPERATION_PJ)}
                  nativeInputProps={{
                    type: 'date',
                    value: dateOperationPJ,
                    onChange: ev=>setDateOperationPJ(ev.target.value)
                  }}
                />
              </div>
              <div className="fr-col-6">
              </div>
              <div className="fr-col-6">
                <Input
                  label={trans(BRIS_PORTE_FIELD_NUMERO_PV)}
                  nativeInputProps={{
                    value: numeroPV,
                    onChange: ev=>setNumeroPV(ev.target.value),
                    maxLength: 255
                  }}
                />
              </div>
              <div className="fr-col-6">
              </div>
              <div className="fr-col-6">
                <Input
                  label={trans(BRIS_PORTE_FIELD_NUMERO_PARQUET)}
                  nativeInputProps={{
                    value: numeroParquet,
                    onChange: ev=>setNumeroParquet(ev.target.value),
                    maxLength: 255
                  }}
                />
              </div>
              <div className="fr-col-6">
              </div>
              <div className="fr-col-6">
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
              <div className="fr-col-6">
              </div>
              <div className="fr-col-4">
              </div>
              <div className="fr-col-8">
                <Button onClick={handleTestEligibilite}>
                {trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_BTN)}
                </Button>
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
              <p>{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_CHAPO)}</p>
              <div className="fr-col-12">
                <ul className="fr-btns-group fr-btns-group--inline-lg">
                  <li>
                    <Submit
                      label={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ERROR_CONTINUE_BTN)}
                      type="secondary"
                    />


                  </li>
                  <li>
                    <Button onClick={handleStop}>
                    {trans(GLOBAL_BTN_STOP)}
                    </Button>
                  </li>
                </ul>
              </div>
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
              <p>{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_ELIGIBLE_CHAPO)}</p>
              <div className="fr-col-12">
                <ul className="fr-btns-group fr-btns-group--inline-lg">
                  <li>
                    <Submit
                      label={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_SUCCESS_CONTINUE_BTN)}
                    />
                  </li>
                </ul>
              </div>
            </>
            }
          </div>
      </modal.Component>
    </form>
  );
}

export default TestEligibilite;
