import React,{useState,useEffect} from 'react';
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Hidden, Submit, Br } from '../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput";
import { trans,
  LOGIN_BTN,
  LOGIN_EMAIL,
  LOGIN_EMAIL_EXAMPLE,
  LOGIN_PASSWORD,
  LOGIN_CONTENT,
  LOGIN_ERROR_TITLE,
  LOGIN_H1
} from '../../../translator';
const FormulaireSimple = ({errorMessage,csrfToken,lastUsername}) => {
  const [email, setEmail]=useState(lastUsername);
  const [password,setPassword]=useState("");
  const [loading,setLoading]=useState(false);
  const handleSubmit = () => {
    if(loading == false)
      event.preventDefault();
  }
  useEffect(() => {
    setLoading(true);
  },[]);

  return (
    <form method="POST" onSubmit={handleSubmit}>
      <Hidden name="_username" value={email} />
      <Hidden name="_password" value={password} />
      <Hidden name="_csrf_token" value={csrfToken} />
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>{trans(LOGIN_H1)}</h1>
          <p>{trans(LOGIN_CONTENT)}</p>
        </div>
        {errorMessage &&
        <div className="fr-col-12">
          <Alert
            description={errorMessage}
            onClose={function noRefCheck(){}}
            severity="error"
            title={trans(LOGIN_ERROR_TITLE)}
          />
        </div>
        }
        <div className="fr-col-12">
          <Input
            label={trans(LOGIN_EMAIL)}
            nativeInputProps={{placeholder: trans(LOGIN_EMAIL_EXAMPLE), value: email, onChange: ev => setEmail(ev.target.value)}}
          />
        </div>
        <div className="fr-col-12">
          <PasswordInput
            label={trans(LOGIN_PASSWORD)}
            nativeInputProps={{value: password, onChange: ev => setPassword(ev.target.value)}}
          />
        </div>
        <div className="fr-col-12">
          <Submit label={trans(LOGIN_BTN)} />
          <Br space={2} />
        </div>
      </div>
    </form>
  )
}

export default FormulaireSimple;
