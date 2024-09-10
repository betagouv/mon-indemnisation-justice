import React,{useState,useEffect} from 'react';
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
      children: "Vérifier mon éligibilité à l'indemnisation"
    }
  ]);

  useEffect(() => setButtons([
    {
      onClick: () => handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet,isErreurPorte),
      doClosesModal: false,
      children: "Vérifier mon éligibilité à l'indemnisation"
    }
  ]),[dateOperationPJ,numeroPV,numeroParquet,isErreurPorte]);

  const resetButtons = () => setButtons([{
    onClick: () => handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet,isErreurPorte),
    doClosesModal: false,
    children: "Vérifier mon éligibilité à l'indemnisation"
  }]);

  function handleTestEligibilite(dateOperationPJ,numeroPV,numeroParquet) {
    event.preventDefault();
    event.stopPropagation();
    if(!dateOperationPJ) {
      alert("Le(s) champ(s) doivent être renseigné(s) pour poursuivre : Date de l'opération de police judiciaire");
      return;
    }
    if(!numeroPV && !numeroParquet) {
      alert("Le(s) champ(s) doivent être renseigné(s) pour poursuivre : Numéro de procès-verbal et/ou Numéro de parquet ou d'instruction");
      return;
    }
    if(false === isErreurPorte) {
      setButtons([
        {
            type: "submit",
            priority: 'secondary',
            doClosesModal: false,
            children: "Effectuer ma demande d'indemnisation"
        },
        {
            onClick: () => handleStop(),
            children: "Abandonner"
        }
      ]);
      setPanel("alert");
    }
    else {
      setButtons([
        {
            type: "submit",
            doClosesModal: false,
            children: "Accéder au formulaire de demande d'indemnisation"
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
    <form onSubmit={handleContinue} name="form" action={Routing.generate("home_test_eligibilite_bris_porte")} method="POST">
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
          Tester mon éligibilité à l'indemnisation
          </Button>
        </div>
      </section>
      <modal.Component
        title="Tester mon éligibilité à l'indemnisation"
        buttons={buttons}
      >
            {("common" == panel) &&

            <>
              <div className="fr-callout">
                <h2 className="fr-callout__title">Comment remplir ce formulaire ?</h2>
                <p className="fr-callout__text">Munissez-vous de l'attestation d'informations qui vous a été remise par
                  le service enquêteur et saisissez les informations qui y sont renseignées dans le formulaire
                  ci-dessous
                </p>
              </div>
              
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-md-7 fr-col-12 fr-col-md-offset-5">
                  <Input
                    label="Date de l'opération de police judiciaire"
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
                        label="Numéro de procès-verbal"
                        nativeInputProps={{
                          value: numeroPV,
                          onChange: ev=>setNumeroPV(ev.target.value),
                          maxLength: 255
                        }}
                      />
                      <Input
                        label="Numéro de parquet ou d'instruction"
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
                    legend="S'agit-il d'une erreur des services de police ?"
                    orientation='horizontal'
                    options={[
                          {
                              label: "Oui",
                              nativeInputProps: {
                                  checked: (isErreurPorte === true),
                                  onChange: ()=> setIsErreurPorte(true)
                              }
                          },
                          {
                              label: "Non",
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
                description="Les informations saisies ne vous permettent pas d'obtenir une indemnisation"
                onClose={function noRefCheck(){}}
                severity="error"
                title="Vous n'êtes pas éligible à l'indemnisation"
              />
              <p className="fr-mt-2w">Le résultat indiqué ci-dessus découle des éléments saisis et est communiqué à titre informatif.</p>
            </>
            }
            {("success" == panel) &&
            <>
              <Alert
                description="Effectuer votre demande amiable d'indemnisation en cliquant sur le bouton ci-dessous."
                onClose={function noRefCheck(){}}
                severity="success"
                title= "Vous êtes éligible à l'indemnisation"
              />
              <p className="fr-mt-2w">Le résultat indiqué ci-dessus découle des éléments saisis et est communiqué à titre informatif.</p>
            </>
            }
      </modal.Component>
    </form>
  );
}

export default TestEligibilite;
