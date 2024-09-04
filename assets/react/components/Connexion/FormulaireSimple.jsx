import React,{useState,useEffect} from 'react';
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Hidden, Submit, Br } from '../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";

const modal = createModal({
    id: "foo-modal",
    isOpenedByDefault: false
});

const FormulaireSimple = ({ errorMessage, csrfToken, lastUsername}) => {

  const isOpen = useIsModalOpen(modal);
  const [email, setEmail]=useState(lastUsername);
  const [password,setPassword]=useState("");
  const [loading,setLoading]=useState(false);
  const [modalEmailSent, setModalEmailSent]=useState(false);

  const handleSubmit = () => {
    if(loading == false)
      event.preventDefault();
  }

  const handleSubmitResetPassword = (event) => {
    setModalEmailSent(true);
    const url = Routing.generate('app_send_reset_password');
    fetch(url,{
      method: "POST",
      body: JSON.stringify({email: email})
    })
      .then((response) => response.json())
      .then((data) => console.log(data))
    ;
    event.preventDefault();
  }

  useEffect(() => {
    setModalEmailSent(false);
    setLoading(true);
  },[]);

  return (
    <section className="pr-form-subscribe">
      <form method="POST" onSubmit={handleSubmit}>
        <Hidden name="_username" value={email} />
        <Hidden name="_password" value={password} />
        <Hidden name="_csrf_token" value={csrfToken} />
            <div className="fr-p-4w">
              <h2>Me connecter à mon espace</h2>
              <p>Pour effectuer ou suivre une demande d'indemnisation, se connecter à son espace personnel</p>
              <p>Sauf mention contraire, tous les champs sont obligatoires</p>
              <div className="fr-grid-row">
                {errorMessage &&
                <div className="fr-col-12 fr-mb-2w">
                  <Alert
                    description={errorMessage}
                    onClose={function noRefCheck(){}}
                    severity="error"
                    small
                  />
                </div>
                }
                <div className="fr-col-12 fr-mb-2w">
                  <Input
                    label="Adresse courriel"
                    hintText="Format attendu : nom@domaine.fr"
                    nativeInputProps={{type: 'email', value: email, onChange: ev => setEmail(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-12 fr-mb-1w">
                  <PasswordInput
                    label="Mot de passe"
                    nativeInputProps={{value: password, onChange: ev => setPassword(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-12  fr-mb-3w">
                  <div className="fr-input-group">
                    <a className="fr-link" href="#" onClick={() => {modal.open()}}>J'ai oublié mon mot de passe</a>
                  </div>
                </div>
                <div className="fr-col-12">
                  <Submit label="Je me connecte à mon espace" />
                </div>
              </div>
            </div>
        <modal.Component title="J'ai oublié mon mot de passe">
          {!modalEmailSent &&
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12">
              <Alert
                small
                onClose={function noRefCheck(){}}
                severity="info"
                description="Saisissez votre email ici, vous recevrez à cette adresse un courriel pour réinitialiser votre mot de passe"
              />
            </div>
            <div className="fr-col-12">
              <Input
                label="Adresse courriel"
                nativeInputProps={{placeholder: "Format attendu : nom@domaine.fr", value: email, onChange: ev => setEmail(ev.target.value)}}
              />
              <Br space={1}/>
            </div>
            <div className="fr-col-12">
              <Button onClick={handleSubmitResetPassword}>
              Recevoir un courriel pour mettre à jour votre mot de passe
              </Button>
            </div>
          </div>
          }
          {modalEmailSent &&
          <Alert
            description="Saisissez votre email ici, vous recevrez à cette adresse un courriel pour réinitialiser votre mot de passe"
            onClose={() => {}}
            severity="success"
            title="Un courriel a été envoyé à votre messagerie"
          />
          }
        </modal.Component>
      </form>
    </section>
  )
}

export default FormulaireSimple;
