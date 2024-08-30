import React,{useState,useEffect} from 'react';
import { Hidden, Submit, Br } from '../../utils/fundamental';
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput";
import { check_empty, check_email, state_error_if_false,
  check_min_length, check_numbers, check_equal } from '../../utils/check_state';

const ReinitialisationMotDePasse = function({csrfToken,successMsg}) {

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
      messages.push({message: `Votre mot de passe doit contenir au moins ${MIN_LENGTH_PASSWORD} caractères`,severity: 'error'});
    if(!check_empty(pwd) && !check_numbers(pwd,NUMBERS_IN_PASSWORD))
      messages.push({message: `Votre mot de passe doit contenir au moins ${NUMBERS_IN_PASSWORD} chiffres` ,severity: 'error'});
    if(cPwd && !check_equal(pwd,cPwd))
      messages.push({message: "Votre mot de passe doit être identique au mot de passe de confirmation", severity: 'error'});
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
          description="Vous pouvez vous connecter dès maintenant sur l'application avec votre nouveau mot de passe"
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
              label="Mot de passe"
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
              label="Confirmer mot de passe"
              nativeInputProps={{value: confirmPassword, onChange: ev => setConfirmPassword(ev.target.value)}}
            />
          </div>
          <div className="fr-col-4">
          </div>
          <div className="fr-col-4">
          </div>
          <div className="fr-col-8">
            <Submit disabled={!submittable} label="Mettre à jour mon mot de passe" />
            <Br space={2} />
          </div>
        </div>
      </form>
    }
    </>
  )
}

export default ReinitialisationMotDePasse;
