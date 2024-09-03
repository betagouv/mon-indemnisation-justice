import React,{useState,useEffect} from 'react';
import { Button } from "@codegouvfr/react-dsfr/Button";
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
    fetch(Routing.generate('_api_requerant_get_collection',{email: email}))
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
    <div>Je certifie avoir lu et accepté les </div>
    <a className="fr-link" href={Routing.generate('app_cgu')} target="_blank">Conditions générales d'utilisation</a>
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
      messages.push({message: `Votre mot de passe doit contenir au moins ${MIN_LENGTH_PASSWORD} caractères`,severity: 'error'});
    if(!check_empty(pwd) && !check_numbers(pwd,NUMBERS_IN_PASSWORD))
      messages.push({message: `Votre mot de passe doit contenir au moins ${NUMBERS_IN_PASSWORD} chiffres`,severity: 'error'});
    if(cPwd && !check_equal(pwd,cPwd))
      messages.push({message: "Votre mot de passe doit être identique au mot de passe de confirmation", severity: 'error'});
    return messages;
  }

  function getErrorEmail(mail) {
    if(!(check_empty(email)||check_email(email)))
      return "Format attendu : nom@domaine.fr";
    if(check_email(email) && !emailFree)
      return "Cette adresse est déjà utilisée, nous vous invitons à réinitialiser votre mot de passe si vous l'avez perdu"
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
              <h2 className="fr-text--sm">Vous possédez déjà un compte ?</h2>
              <Button linkProps={{ href: Routing.generate('app_login') }} priority="secondary">
              Se connecter à mon espace
              </Button>
            </div>
          </div>
          <div className="fr-col-12">
            <div className="fr-p-4w">
              <h2>Créer un compte</h2>
              <Alert
                title="Pourquoi ?"
                description={<p>L'accès au formulaire de demande d'indemnisation recquiert la création d'un compte</p>}
                severity="info"
                small
              />
              <p className="fr-my-4w">Sauf mention contraire, tous les champs sont obligatoires</p>
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-4">
                  <Civilite civilite={civilite} setCivilite={setCivilite} defaultOptionText={" "} />
                </div>
                <div className="fr-col-8">
                  <Input
                    label="Prénom(s)"
                    nativeInputProps={{name: 'prenom1', value: prenom1, onChange: ev => setPrenom1(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-6">
                  <Input
                    label="Nom de naissance"
                    nativeInputProps={{name: 'nomNaissance', value: nomNaissance, onChange: ev => setNomNaissance(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-6">
                <Input
                  label="Nom d'usage"
                  nativeInputProps={{name:'nom', value: nom, onChange: ev => setNom(ev.target.value)}}
                />
                </div>

                <div className="fr-col-12">
                  <Input
                    label="Adresse courriel"
                    state={state_error_if_false(check_empty(email)||(check_email(email)&&emailFree))}
                    stateRelatedMessage={getErrorEmail(email)}
                    nativeInputProps={{name: 'email', value: email, onChange: ev => setEmail(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-5">
                  <PasswordInput
                    label="Mot de passe"
                    messages={manageMsgPassword(password,confirmPassword)}
                    nativeInputProps={{name: 'password', value: password, onChange: ev => setPassword(ev.target.value)}}
                  />
                </div>
                <div className="fr-col-7">
                  <PasswordInput
                    label="Confirmation du mot de passe"
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
                  <Submit disabled={!submittable} label="Valider mon inscription et poursuivre ma demande" />
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
