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
  SECURITY_INSCRIPTION_TITLE,
  SECURITY_INSCRIPTION_SUBMIT,
  SECURITY_INSCRIPTION_ACCOUNT_ALLREADY_EXIST,
  SECURITY_INSCRIPTION_CONNECT_SPACE_BTN
} from '../../translator';
import { Checkbox } from "@codegouvfr/react-dsfr/Checkbox";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { PasswordInput } from "@codegouvfr/react-dsfr/blocks/PasswordInput"
import { Br, Submit, Hidden } from '../utils/fundamental';
import { check_empty, check_email, state_error_if_false } from '../utils/check_state';
import Civilite from './Civilite';

const Inscription = ({user,csrfToken}) => {
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
  const toggleCguAccepted = () => setCguAccepted(!cguAccepted);
  const textCgu = <>
    <div>{trans(SECURITY_INSCRIPTION_CGU_PREFIX)}</div>
    <a href={Routing.generate('app_cgu')} target="_blank">{trans(SECURITY_INSCRIPTION_CGU_CONTENT)}</a>
  </>;
  const checkValidity = () => {
    return
      !check_empty(civilite) &&
      !check_empty(prenom1) &&
      !check_empty(nom) &&
      !check_empty(email) &&
      !check_email(email)
    ;
  }

  useEffect(() => {
    setLoading(true);
  },[]);
  useEffect(() => {
    setSubmittable(checkValidity());
  },[civilite, prenom1, nom, email]);

  const handleSubmit = (event) => {
    if(loading == false)
      event.preventDefault();
  }
  return (
    <form method="POST" action={Routing.generate('app_inscription')} onSubmit={handleSubmit}>
      <Hidden name="_csrf_token" value={csrfToken} />
      <Hidden name="type" value={"BRI"} />
      <h5>{trans(SECURITY_INSCRIPTION_ACCOUNT_ALLREADY_EXIST)}</h5>
      <Button linkProps={{ href: Routing.generate('app_login') }} priority="secondary">
      {trans(SECURITY_INSCRIPTION_CONNECT_SPACE_BTN)}
      </Button>
      <Br space={2} />
      <hr/>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>{trans(SECURITY_INSCRIPTION_TITLE)}</h1>
        </div>
        <div className="fr-col-12">
          <Alert
            description={trans(SECURITY_INSCRIPTION_CHAPO)}
            severity="info"
            small
          />
        </div>
        <div className="fr-col-12">
          <b>{trans(SECURITY_INSCRIPTION_DESCRIPTION)}</b>
        </div>
        <div className="fr-col-4">
          <Civilite civilite={civilite} setCivilite={setCivilite} />
        </div>
        <div className="fr-col-8">
          <Input
            label={trans(USER_FIELD_PRENOMS)}
            nativeInputProps={{name:'prenom1', placeholder: trans(USER_FIELD_PRENOMS), value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
          />
        </div>
        <div className="fr-col-6">
        <Input
          label={trans(USER_FIELD_NOM)}
          nativeInputProps={{name:'nom', value: nom, onChange: ev => setNom(ev.target.value)}}
        />
        </div>
        <div className="fr-col-6">
          <Input
            label={trans(USER_FIELD_NOM_NAISSANCE)}
            nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
          />
        </div>
        <div className="fr-col-12">
          <Input
            label={trans(LOGIN_EMAIL)}
            state={state_error_if_false(check_empty(email)||check_email(email))}
            stateRelatedMessage={!(check_empty(email)||check_email(email)) ? trans(LOGIN_EMAIL_EXAMPLE) : ""}
            nativeInputProps={{name: 'email', value: email, onChange: ev => setEmail(ev.target.value)}}
          />
        </div>
        <div className="fr-col-5">
          <PasswordInput
            label={trans(LOGIN_PASSWORD)}
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
    </form>
  );
}

export default Inscription;
