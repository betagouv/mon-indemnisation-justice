import React,{useState,useEffect} from 'react';
import { Button } from "@codegouvfr/react-dsfr/Button";
import {trans,
  LOGIN_EMAIL,
  LOGIN_PASSWORD,
  LOGIN_CONFIRM_PASSWORD,
  USER_FIELD_NOM_NAISSANCE,
  USER_FIELD_PRENOMS,
  USER_FIELD_NOM,
  LOGIN_EMAIL_EXAMPLE,
  SECURITY_INSCRIPTION_CGU_PREFIX,
  SECURITY_INSCRIPTION_CGU_CONTENT,
  SECURITY_INSCRIPTION_CHAPO,
  SECURITY_INSCRIPTION_DESCRIPTION,
  SECURITY_INSCRIPTION_MESSAGE_EMAIL_ALLREADY_EXIST,
  SECURITY_INSCRIPTION_MESSAGE_INVALID_LENGTH_PASSWORD,
  SECURITY_INSCRIPTION_MESSAGE_INVALID_NUMBER_PASSWORD,
  SECURITY_INSCRIPTION_MESSAGE_SAME_PASSWORD,
  SECURITY_INSCRIPTION_TITLE,
  SECURITY_INSCRIPTION_SUBMIT,
  SECURITY_INSCRIPTION_ACCOUNT_ALLREADY_EXIST,
  SECURITY_INSCRIPTION_CONNECT_SPACE_BTN,
  GLOBAL_OPTIONAL
} from '../../translator';
import { Checkbox } from "@codegouvfr/react-dsfr/Checkbox";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput"
import { Br, Submit, Hidden } from '../utils/fundamental';
import { check_empty, check_email, state_error_if_false,
  check_min_length, check_numbers, check_equal } from '../utils/check_state';
import { cast_number } from '../utils/cast';
import Civilite from './Civilite';

const Inscription = ({user,csrfToken}) => {

  const MIN_LENGTH_PASSWORD=8;
  const NUMBERS_IN_PASSWORD=1;

  const check_email_exists = () => {
    setWaiting(true);
    if(!check_email(email))
      return false;
    fetch(Routing.generate('_api_user_get_collection',{email: email}))
      .then((response) => response.json())
      .then((data) => {setEmailFree(data["hydra:totalItems"]==0);setWaiting(false);})
    ;
  }
  const [civilite,setCivilite]=useState(user.personnePhysique.civilite??"");
  const [prenom1, setPrenom1]=useState(user.personnePhysique.prenom1??"");
  const [nom, setNom]=useState(user.personnePhysique.nom??"");
  const [nomNaissance, setNomNaissance]=useState(user.personnePhysique.nomNaissance??"");
  const [email, setEmail]=useState(user.personnePhysique.email??"");
  const [password, setPassword]=useState("");
  const [confirmPassword, setConfirmPassword]=useState("");
  const [cguAccepted, setCguAccepted]=useState(false);
  const [loading, setLoading]=useState(false);
  const [submittable, setSubmittable]=useState(false);
  const [emailFree, setEmailFree]=useState(false);
  const toggleCguAccepted = () => setCguAccepted(!cguAccepted);
  const [waiting, setWaiting]=useState(false);
  const textCgu = <>
    <div>{trans(SECURITY_INSCRIPTION_CGU_PREFIX)}</div>
    <a className="fr-link" href={Routing.generate('app_cgu')} target="_blank">{trans(SECURITY_INSCRIPTION_CGU_CONTENT)}</a>
  </>;
  const checkValidity = () => {
    const test =
      !check_empty(password) &&
      check_min_length(password,MIN_LENGTH_PASSWORD) &&
      check_numbers(password,NUMBERS_IN_PASSWORD) &&
      !check_empty(civilite) &&
      !check_empty(prenom1) &&
      !check_empty(nomNaissance) &&
      !check_empty(email) &&
      check_email(email) &&
      check_equal(password,confirmPassword) &&
      cguAccepted &&
      emailFree &&
      !waiting
    ;
    return test;
  }

  useEffect(() => {
    setLoading(true);
  },[]);
  useEffect(() => {
    setSubmittable(checkValidity());
  },[civilite, prenom1, nomNaissance, email, password, confirmPassword, cguAccepted, emailFree, waiting]);

  useEffect(() => {check_email_exists();},[email]);

  const handleSubmit = (event) => {
    if(loading == false)
      event.preventDefault();
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

  function getErrorEmail(mail) {
    if(!(check_empty(email)||check_email(email)))
      return trans(LOGIN_EMAIL_EXAMPLE);
    if(check_email(email) && !emailFree)
      return trans(SECURITY_INSCRIPTION_MESSAGE_EMAIL_ALLREADY_EXIST);
    return "";
  }

  return (
    <section className="pr-form-subscribe">
      <form method="POST" action={Routing.generate('app_inscription')} onSubmit={handleSubmit}>
        <Hidden name="_csrf_token" value={csrfToken} />
        <Hidden name="type" value={"BRI"} />
        <Hidden name="civilite" value={cast_number(civilite)} />
        <div className="fr-grid-row">
          <div className="pr-form-subscribe_had-account fr-col-12">
            <div className="fr-p-4w">
              <h2 className="fr-text--sm">{trans(SECURITY_INSCRIPTION_ACCOUNT_ALLREADY_EXIST)}</h2>
              <Button linkProps={{ href: Routing.generate('app_login') }} priority="secondary">
              {trans(SECURITY_INSCRIPTION_CONNECT_SPACE_BTN)}
              </Button>
            </div>
          </div>
          <div className="fr-col-12">
            <div className="fr-p-4w">
              <h2>{trans(SECURITY_INSCRIPTION_TITLE)}</h2>
              <Alert
                description=<p>{trans(SECURITY_INSCRIPTION_CHAPO)}</p>
                severity="info"
                small
              />
              <p className="fr-my-4w">{trans(SECURITY_INSCRIPTION_DESCRIPTION)}</p>
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-4">
                  <Civilite civilite={civilite} setCivilite={setCivilite} defaultOptionText={" "} />
                </div>
                <div className="fr-col-8">
                  <Input
                    label={trans(USER_FIELD_PRENOMS)}
                    nativeInputProps={{value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-6">
                  <Input
                    label={trans(USER_FIELD_NOM_NAISSANCE)}
                    nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-6">
                <Input
                  label={trans(USER_FIELD_NOM)+" "+trans(GLOBAL_OPTIONAL)}
                  nativeInputProps={{name:'nom', value: nom, onChange: ev => setNom(ev.target.value)}}
                />
                </div>

                <div className="fr-col-12">
                  <Input
                    label={trans(LOGIN_EMAIL)}
                    state={state_error_if_false(check_empty(email)||(check_email(email)&&emailFree))}
                    stateRelatedMessage={getErrorEmail(email)}
                    nativeInputProps={{name: 'email', value: email, onChange: ev => setEmail(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-5">
                  <PasswordInput
                    label={trans(LOGIN_PASSWORD)}
                    messages={manageMsgPassword(password,confirmPassword)}
                    nativeInputProps={{name: 'password', value: password, onChange: ev => setPassword(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-7">
                  <PasswordInput
                    label={trans(LOGIN_CONFIRM_PASSWORD)}
                    nativeInputProps={{value: confirmPassword, onChange: ev => setConfirmPassword(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-12">
                  <Checkbox
                    options={[
                      {
                        label: textCgu,
                        nativeInputProps: {
                          value: true,
                          onChange: ev => toggleCguAccepted()
                        }
                      }
                    ]}
                  />
                </div>
                <div className="fr-col-12">
                  <Submit disabled={!submittable} label={trans(SECURITY_INSCRIPTION_SUBMIT)} />
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>
  );
}

export default Inscription;
