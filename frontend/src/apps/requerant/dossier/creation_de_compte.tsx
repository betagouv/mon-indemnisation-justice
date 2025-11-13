import "reflect-metadata";
import {
  Civilite,
  Inscription,
} from "@/apps/requerant/dossier/models/Inscription";
import FranceConnectButton from "@codegouvfr/react-dsfr/FranceConnectButton";
import { instanceToPlain, plainToInstance } from "class-transformer";
import { validate } from "class-validator";
import _ from "lodash";
import { autorun, observable, ObservableMap } from "mobx";
import { observer } from "mobx-react-lite";
import React, { useState } from "react";
// En attente de React 19
//import { useFormStatus } from "react-dom";
import ReactDOM from "react-dom/client";

const args = JSON.parse(
  document.getElementById("react-arguments")?.textContent ?? "{}",
);

interface Routes {
  connexion: string;
  inscriptionFranceConnect: string;
  cgu: string;
}

const token: string = args.token;
const routes: Routes = args.routes as Routes;
const inscription = plainToInstance(Inscription, args.inscription);
let erreurs = observable.map<string, string>([]);

autorun(async (i) => {
  const err = await validate(inscription);
  erreurs.replace(
    err.map((error) => [
      error.property.replace(/^_/, ""),
      Object.values(error.constraints).at(0),
    ]),
  );
});

const CreationDeCompteApp = observer(function CreationDeCompteApp({
  inscription,
  token,
  routes,
  erreurs = undefined,
}: {
  inscription: Inscription;
  token: string;
  routes: Routes;
  erreurs?: ObservableMap<string, string>;
}) {
  const [motDePasseRevele, setMotDePasseRevele] = useState(false);
  const [confirmationRevelee, setConfirmationRevelee] = useState(false);
  //const { pending: sauvegardeEnCours } = useFormStatus();
  const [sauvegardeEnCours, setSauvegardeEnCours] = useState(false);
  const [inscriptionParEmail, setInscriptionParEmail] = useState(false);

  const creerLeCompte = async function () {
    setSauvegardeEnCours(true);

    const response = await fetch(`/bris-de-porte/creer-compte`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-Csrf-Token": token,
      },
      body: JSON.stringify(
        instanceToPlain(inscription, { excludePrefixes: ["_"] }),
      ),
    });

    if (response.ok) {
      // Recharger la page afin d'être redirigé si l'inscription a bien fonctionné
      window.location.reload();
    }

    setSauvegardeEnCours(false);
  };

  return (
    <div className="fr-container fr-my-3w">
      <div className="fr-stepper">
        <h2 className="fr-stepper__title">
          Création de votre compte
          <span className="fr-stepper__state">Étape 2 sur 3</span>
        </h2>
        <div
          className="fr-stepper__steps"
          data-fr-current-step="2"
          data-fr-steps="3"
        ></div>
        <p className="fr-stepper__details">
          <span className="fr-text--bold">Étape suivante :</span> Finaliser la
          création de votre compte
        </p>
      </div>

      <div className="fr-grid-row fr-mb-6w">
        <section className="pr-keyboard-hands fr-col-lg-6 fr-hidden fr-unhidden-lg">
          <div className="pic-keyboard-hands"></div>
        </section>
        <div className="fr-col-lg-6 fr-col-12">
          <section
            className="pr-form-subscribe"
            style={{ border: "1px solid var(--border-default-grey)" }}
          >
            <form>
              <input type="hidden" name="_token" value={token} />
              <div className="fr-grid-row">
                <div className="pr-form-subscribe_had-account fr-col-12"></div>

                <div className="fr-col-12">
                  <div className=" fr-p-0 fr-p-4w">
                    <div className="fr-alert fr-alert--info fr-alert--sm">
                      <div>
                        <p>
                          L'accès au formulaire de demande d'indemnisation
                          requiert la création d'un compte
                        </p>
                      </div>
                    </div>

                    <div className="fr-py-2w">
                      <p className="fr-text--sm">
                        Vous possédez déjà un compte ?{" "}
                        <a
                          href={routes.connexion}
                          className="fr-link fr-icon-arrow-right-line fr-link--icon-right"
                        >
                          Connectez-vous
                        </a>
                      </p>
                    </div>

                    <div className="fr-grid-row fr-grid-row--center">
                      <div className="fr-notice fr-notice--info fr-mb-2w">
                        <div className="fr-container">
                          <div className="fr-notice__body">
                            <p>
                              FranceConnect est la solution proposée par l’État
                              pour sécuriser et simplifier la connexion à vos
                              services en ligne
                            </p>
                          </div>
                        </div>
                      </div>

                      <FranceConnectButton
                        url={routes.inscriptionFranceConnect}
                      />
                    </div>

                    <div className="fr-section-separateur--ligne">
                      <span>ou</span>
                    </div>

                    {!inscriptionParEmail && (
                      <div className="fr-grid-row fr-grid-row--center">
                        <button
                          className="fr-btn"
                          onClick={() => setInscriptionParEmail(true)}
                        >
                          S'inscrire avec une adresse email
                        </button>
                      </div>
                    )}

                    {inscriptionParEmail && (
                      <>
                        <p className="fr-my-2w">
                          Tous les champs sont obligatoires
                        </p>
                        <div className="fr-grid-row fr-grid-row--gutters">
                          <div className="fr-col-lg-4 fr-col-12">
                            <div className="fr-select-group">
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-civilite"
                              >
                                Civilité
                              </label>
                              <select
                                name="civilite"
                                className="fr-select"
                                id="inscription-champs-civilite"
                                aria-describedby="select-:r4:-desc"
                                defaultValue={
                                  inscription.civilite
                                    ? Object.keys(Civilite).find(
                                        (key) =>
                                          Civilite[key] ===
                                          inscription.civilite,
                                      )
                                    : ""
                                }
                                onChange={(e) =>
                                  (inscription.civilite = Civilite[
                                    e.target.value
                                  ] as Civilite)
                                }
                              >
                                <option value="" disabled hidden></option>
                                {Object.entries(Civilite).map(
                                  ([nom, civilite]) => (
                                    <option key={civilite} value={nom}>
                                      {civilite.valueOf()}
                                    </option>
                                  ),
                                )}
                              </select>
                            </div>
                          </div>
                          <div className="fr-col-lg-8 fr-col-12">
                            <div className="fr-input-group">
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-prenom"
                              >
                                Prénom
                              </label>
                              <input
                                name="prenom"
                                id="inscription-champs-prenom"
                                className="fr-input"
                                aria-describedby="input-:r5:-desc-error"
                                type="text"
                                defaultValue={inscription.prenom}
                                onChange={(e) =>
                                  (inscription.prenom = e.target.value)
                                }
                              />
                            </div>
                          </div>
                          <div className="fr-col-lg-6 fr-col-12">
                            <div className="fr-input-group">
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-nom"
                              >
                                Nom de naissance
                              </label>
                              <input
                                name="nomNaissance"
                                id="inscription-champs-nom"
                                className="fr-input"
                                aria-describedby="input-:r6:-desc-error"
                                type="text"
                                defaultValue={inscription.nomNaissance}
                                onChange={(e) =>
                                  (inscription.nomNaissance = e.target.value)
                                }
                              />
                            </div>
                          </div>
                          <div className="fr-col-lg-6 fr-col-12">
                            <div className="fr-input-group">
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-nom-usage"
                              >
                                Nom d'usage
                              </label>
                              <input
                                name="nom"
                                className="fr-input"
                                type="text"
                                id="inscription-champs-nom-usage"
                                defaultValue={inscription.nom}
                                onChange={(e) =>
                                  (inscription.nom = e.target.value)
                                }
                              />
                            </div>
                          </div>
                          <div className="fr-col-6">
                            <div
                              className={`fr-input-group  ${false ? "fr-input--error" : ""}`}
                            >
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-courriel"
                              >
                                Adresse courriel
                              </label>
                              <input
                                name="courriel"
                                id="inscription-champs-courriel"
                                className={`fr-input ${false ? "fr-input--error" : ""}`}
                                aria-describedby="inscription-champs-courriel-error"
                                type="text"
                                defaultValue={inscription.courriel}
                                onChange={(e) =>
                                  _.debounce(
                                    () =>
                                      (inscription.courriel = e.target.value),
                                    350,
                                  )()
                                }
                              />
                              {erreurs.has("courriel") && (
                                <p
                                  id="inscription-champs-courriel-error"
                                  className="fr-error-text"
                                >
                                  {erreurs.get("courriel")}
                                </p>
                              )}
                            </div>
                          </div>
                          <div className="fr-col-6">
                            <div
                              className={`fr-input-group ${false ? "fr-input--error" : ""}`}
                            >
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-telephone"
                              >
                                Numéro de téléphone
                              </label>
                              <input
                                name="telephone"
                                id="inscription-champs-telephone"
                                className={`fr-input ${false ? "fr-input--error" : ""}`}
                                aria-describedby="inscription-champs-telephone-error"
                                type="text"
                                defaultValue={inscription.telephone}
                                onChange={(e) =>
                                  (inscription.telephone = e.target.value)
                                }
                              />
                            </div>
                          </div>
                          {/* Mot de passe */}
                          <div className="fr-input-group fr-col-lg-6 fr-col-12">
                            <div className="fr-password">
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-mot-de-passe"
                              >
                                Mot de passe
                                <span className="fr-hint-text">
                                  Au moins 8 caractères, dont 1 chiffre
                                </span>
                              </label>
                              <div className="fr-input-wrap">
                                <input
                                  name="motDePasse"
                                  className={`fr-password__input fr-input ${false ? "fr-input--error" : ""}`}
                                  id="inscription-champs-mot-de-passe"
                                  aria-describedby="inscription-champs-mot-de-passe-error"
                                  type={motDePasseRevele ? "text" : "password"}
                                  defaultValue={inscription.motDePasse}
                                  onChange={(e) =>
                                    (inscription.motDePasse = e.target.value)
                                  }
                                />
                                <button
                                  type="button"
                                  tabIndex={-1}
                                  className="fr-btn fr-btn--tertiary-no-outline fr-btn--input-overlay"
                                  onClick={() =>
                                    setMotDePasseRevele(!motDePasseRevele)
                                  }
                                  title={
                                    motDePasseRevele ? "Masque" : "Afficher"
                                  }
                                >
                                  <span
                                    className={`${motDePasseRevele ? "fr-icon-eye-off-line" : "fr-icon-eye-line"}`}
                                    aria-hidden="true"
                                  ></span>
                                </button>
                              </div>
                              <p
                                id="inscription-champs-mot-de-passe-error"
                                hidden={true}
                                className={`fr-error-text ${true ? "fr-hidden" : ""}`}
                              >
                                {/*{% verbatim %}{{ erreurs?.motDePasse }}{% endverbatim %}*/}
                              </p>
                            </div>
                          </div>
                          <div className="fr-input-group fr-col-lg-6 fr-col-12">
                            <div
                              className="fr-password"
                              data-fr-js-password="true"
                            >
                              <label
                                className="fr-label"
                                htmlFor="inscription-champs-confirmation"
                              >
                                Confirmation du mot de passe
                                <span className="fr-hint-text">&nbsp;</span>
                              </label>
                              <div className="fr-input-wrap">
                                <input
                                  name="confirmation"
                                  className={`fr-password__input fr-input ${false ? "fr-input--error" : ""}`}
                                  id="inscription-champs-confirmation"
                                  aria-describedby="inscription-champs-confirmation-error"
                                  type={
                                    confirmationRevelee ? "text" : "password"
                                  }
                                  defaultValue={inscription.confirmation}
                                  onChange={(e) =>
                                    (inscription.confirmation = e.target.value)
                                  }
                                />
                                <button
                                  type="button"
                                  tabIndex={-1}
                                  className="fr-btn fr-btn--tertiary-no-outline fr-btn--input-overlay"
                                  onClick={() =>
                                    setConfirmationRevelee(!confirmationRevelee)
                                  }
                                  title={`${confirmationRevelee ? "Masque" : "Afficher"}`}
                                >
                                  <span
                                    className={`${confirmationRevelee ? "fr-icon-eye-off-line" : "fr-icon-eye-line"}`}
                                    aria-hidden="true"
                                  ></span>
                                </button>
                              </div>
                              <p
                                id="inscription-champs-confirmation-error"
                                className={`fr-error-text ${true ? "fr-hidden" : ""}`}
                              >
                                {/*{% verbatim %}{{ erreurs?.confirmation }}{% endverbatim %}*/}
                              </p>
                            </div>
                          </div>

                          <div className="fr-col-12">
                            <fieldset className="fr-fieldset">
                              <div className="fr-fieldset__content">
                                <div className="fr-checkbox-group">
                                  <input
                                    type="checkbox"
                                    id="inscription-champs-cgu-ok"
                                    name="cguOk"
                                    checked={inscription.cguOk}
                                    onChange={(e) =>
                                      (inscription.cguOk = e.target.checked)
                                    }
                                  />
                                  <label
                                    className="fr-label"
                                    htmlFor="inscription-champs-cgu-ok"
                                  >
                                    Je certifie avoir lu et accepté les&nbsp;
                                    <a
                                      className="fr-link"
                                      href={routes.cgu}
                                      target="_blank"
                                    >
                                      Conditions générales d'utilisation
                                    </a>
                                  </label>
                                </div>
                              </div>
                            </fieldset>
                          </div>

                          <div className="fr-col-12">
                            <button
                              className="fr-btn"
                              type="submit"
                              disabled={erreurs.size > 0 || sauvegardeEnCours}
                              onClick={async (e) => {
                                e.preventDefault();
                                await creerLeCompte();
                              }}
                            >
                              {sauvegardeEnCours
                                ? "Inscription en cours"
                                : "Valider mon inscription et poursuivre ma demande"}
                            </button>
                          </div>
                        </div>
                      </>
                    )}
                  </div>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  );
});

ReactDOM.createRoot(document.getElementById("react-app") as HTMLElement).render(
  <React.StrictMode>
    <>
      <CreationDeCompteApp
        inscription={inscription}
        token={token}
        routes={routes}
        erreurs={erreurs}
      />
    </>
  </React.StrictMode>,
);
