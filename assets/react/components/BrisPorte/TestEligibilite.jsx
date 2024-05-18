import React,{useState,useEffect} from 'react';
import {trans, BRIS_PORTE_TEST_ELIGIBILITE_H2,
  BRIS_PORTE_TEST_ELIGIBILITE_DESCRIPTION,
  BRIS_PORTE_TEST_ELIGIBILITE_BTN,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_CHAPO,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_TITLE,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_CHAPO,
  BRIS_PORTE_TEST_ELIGIBILITE_MODAL_BTN,
  BRIS_PORTE_FIELD_DATE_OPERATION_PJ,
  BRIS_PORTE_FIELD_IS_ERREUR_PORTE,
  BRIS_PORTE_FIELD_NUMERO_PV,
  BRIS_PORTE_FIELD_NUMERO_PARQUET,
  GLOBAL_NO,
  GLOBAL_YES
} from '../../../translator';
import parse from 'html-react-parser';
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
    setPanel("alert");
  }

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-8">
          <h2>{trans(BRIS_PORTE_TEST_ELIGIBILITE_H2)}</h2>
          {parse(trans(BRIS_PORTE_TEST_ELIGIBILITE_DESCRIPTION))}
          <p>
            <Button onClick={() => {modal.open()}}>
            {trans(BRIS_PORTE_TEST_ELIGIBILITE_BTN)}
            </Button>
          </p>
        </div>
        <div className="fr-col-4">
        </div>
      </div>
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
                    type: 'date',value: dateOperationPJ, onChange: ev=>setDateOperationPJ(ev.target.value)
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
                description="This is the description"
                onClose={function noRefCheck(){}}
                severity="error"
                title={trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_TITLE)}
              />
              <p>{trans(BRIS_PORTE_TEST_ELIGIBILITE_MODAL_NO_ELIGIBLE_CHAPO)}</p>
            </>
            }
          </div>
      </modal.Component>
    </>
  );
}

export default TestEligibilite;
