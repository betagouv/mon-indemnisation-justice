import React,{useState,useEffect} from 'react';
import { Hidden, Submit, Br } from '../../utils/fundamental';
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput";
import { check_empty, check_email, state_error_if_false,
  check_min_length, check_numbers, check_equal } from '../../utils/check_state';
import { trans,
  SECURITY_RESET_PASSWORD_TITLE,
  LOGIN_BTN,
  LOGIN_EMAIL,
  SECURITY_INSCRIPTION_MESSAGE_SAME_PASSWORD,
  SECURITY_INSCRIPTION_MESSAGE_INVALID_NUMBER_PASSWORD,
  SECURITY_INSCRIPTION_MESSAGE_INVALID_LENGTH_PASSWORD,
  LOGIN_CONFIRM_PASSWORD,
  SECURITY_RESET_PASSWORD_SUCCESS_PASSWORD_RESETED_DESCRIPTION,
  LOGIN_PASSWORD
} from '../../../translator';

const ReinitialisationMotDePasse = function({user,csrfToken,successMsg}) {

  const MIN_LENGTH_PASSWORD=8;
  const NUMBERS_IN_PASSWORD=1;
  const [password,setPassword]=useState("");
  const [loading,setLoading]=useState(false);
  const [confirmPassword,setConfirmPassword]=useState("");
  const [submittable, setSubmittable]=useState(false);

  const checkValidity = () => {
    const test =
      !check_empty(password) &&
      check_min_length(password,MIN_LENGTH_PASSWORD) &&
      check_numbers(password,NUMBERS_IN_PASSWORD) &&
      check_equal(password,confirmPassword)
    ;
    return test;
  }

  const manageMsgPassword = (pwd,cPwd) => {
    const messages=[];
    if(!check_empty(pwd) && !check_min_length(pwd,MIN_LENGTH_PASSWORD))
      messages.push({message: trans(SECURITY_INSCRIPTION_MESSAGE_INVALID_LENGTH_PASSWORD).replace('%length%', MIN_LENGTH_PASSWORD),severity: 'error'});
    if(!check_empty(pwd) && !check_numbers(pwd,NUMBERS_IN_PASSWORD))
      messages.push({message: trans(SECURITY_INSCRIPTION_MESSAGE_INVALID_NUMBER_PASSWORD).replace('%length%', NUMBERS_IN_PASSWORD),severity: 'error'});
    if(cPwd && !check_equal(pwd,cPwd))
      messages.push({message: trans(SECURITY_INSCRIPTION_MESSAGE_SAME_PASSWORD),severity: 'error'});
    return messages;
  }

  const handleSubmit = () => {
    if(loading == false)
      event.preventDefault();
  }

  useEffect(() => {
    setLoading(true);
  },[]);

  useEffect(() => {
    setSubmittable(checkValidity());
  },[password, confirmPassword]);

  return (
    <>
    {successMsg &&
      <>
        <Alert
          description={trans(SECURITY_RESET_PASSWORD_SUCCESS_PASSWORD_RESETED_DESCRIPTION)}
          onClose={() => {}}
          severity="success"
          title={successMsg}
        />
        <Br space={2}/>
      </>
    }
    {!successMsg &&
      <form method="POST" onSubmit={handleSubmit}>
        <Hidden name="_password" value={password} />
        <Hidden name="_csrf_token" value={csrfToken} />
        <div className="fr-grid-row">
          <div className="fr-col-4">
          </div>
          <div className="fr-col-4">
            <PasswordInput
              label={trans(LOGIN_PASSWORD)}
              messages={manageMsgPassword(password,confirmPassword)}
              nativeInputProps={{name: 'password', value: password, onChange: ev => setPassword(ev.target.value)}}
            />
          </div>
          <div className="fr-col-4">
          </div>
          <div className="fr-col-4">
          </div>
          <div className="fr-col-4">
            <PasswordInput
              label={trans(LOGIN_CONFIRM_PASSWORD)}
              nativeInputProps={{value: confirmPassword, onChange: ev => setConfirmPassword(ev.target.value)}}
            />
          </div>
          <div className="fr-col-4">
          </div>
          <div className="fr-col-4">
          </div>
          <div className="fr-col-8">
            <Submit disabled={!submittable} label={trans(SECURITY_RESET_PASSWORD_TITLE)} />
            <Br space={2} />
          </div>
        </div>
      </form>
    }
    </>
  )
}

export default ReinitialisationMotDePasse;
