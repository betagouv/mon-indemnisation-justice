import React,{useState,useEffect} from 'react';
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Hidden, Submit, Br } from '../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import { trans,
  LOGIN_BTN,
  LOGIN_EMAIL,
  GLOBAL_INFORMATIONS_REQUIREMENT,
  SECURITY_RESET_PASSWORD_SUCCESS_TITLE,
  SECURITY_RESET_PASSWORD_SUCCESS_DESCRIPTION,
  SECURITY_RESET_PASSWORD_SUBMIT,
  LOGIN_EMAIL_EXAMPLE,
  LOGIN_PASSWORD,
  LOGIN_CONTENT,
  LOGIN_ERROR_TITLE,
  LOGIN_FORGOTTEN_PASSWORD,
  LOGIN_H1,
  SECURITY_RESET_PASSWORD_DESCRIPTION
} from '../../../translator';

const modal = createModal({
    id: "foo-modal",
    isOpenedByDefault: false
});

const FormulaireSimple = ({errorMessage,csrfToken,lastUsername}) => {

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
              <h2>{trans(LOGIN_H1)}</h2>
              <p>{trans(LOGIN_CONTENT)}</p>
              <p>{trans(GLOBAL_INFORMATIONS_REQUIREMENT)}</p>
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
                    label={trans(LOGIN_EMAIL)}
                    hintText={trans(LOGIN_EMAIL_EXAMPLE)}
                    nativeInputProps={{type: 'email', value: email, onChange: ev => setEmail(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-12 fr-mb-1w">
                  <PasswordInput
                    label={trans(LOGIN_PASSWORD)}
                    nativeInputProps={{value: password, onChange: ev => setPassword(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-12  fr-mb-3w">
                  <div className="fr-input-group">
                    <a className="fr-link" href="#" onClick={() => {modal.open()}}>{trans(LOGIN_FORGOTTEN_PASSWORD)}</a>
                  </div>
                </div>
                <div className="fr-col-12">
                  <Submit label={trans(LOGIN_BTN)} />
                </div>
              </div>
            </div>
        <modal.Component title={trans(LOGIN_FORGOTTEN_PASSWORD)}>
          {!modalEmailSent &&
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12">
              <Alert
                small
                onClose={function noRefCheck(){}}
                severity="info"
                description={trans(SECURITY_RESET_PASSWORD_DESCRIPTION)}
              />
            </div>
            <div className="fr-col-12">
              <Input
                label={trans(LOGIN_EMAIL)}
                nativeInputProps={{placeholder: trans(LOGIN_EMAIL_EXAMPLE), value: email, onChange: ev => setEmail(ev.target.value)}}
              />
              <Br space={1}/>
            </div>
            <div className="fr-col-12">
              <Button onClick={handleSubmitResetPassword}>
              {trans(SECURITY_RESET_PASSWORD_SUBMIT)}
              </Button>
            </div>
          </div>
          }
          {modalEmailSent &&
          <Alert
            description={trans(SECURITY_RESET_PASSWORD_SUCCESS_DESCRIPTION)}
            onClose={() => {}}
            severity="success"
            title={trans(SECURITY_RESET_PASSWORD_SUCCESS_TITLE)}
          />
          }
        </modal.Component>
      </form>
    </section>
  )
}

export default FormulaireSimple;
