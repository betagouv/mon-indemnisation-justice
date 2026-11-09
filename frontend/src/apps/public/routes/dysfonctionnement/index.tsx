import { FormulaireConnexion } from "@/apps/public/components/authentification/FormulaireConnexion.tsx";
import { LienFranceConnect } from "@/apps/public/components/authentification/LienFranceConnect.tsx";
import {
  ProfilCreationCompte,
  saveCreationCompte,
} from "@/apps/public/services/creationCompteStore.ts";
import { Layout } from "@/apps/public/components/Layout.tsx";
import {
  CreationDeCompte,
  motDePasseValide,
} from "@common/composants/authentification/CreationDeCompte.tsx";
import {
  EcranFranceConnect,
  TuileFranceConnect,
} from "@common/composants/authentification/EcranFranceConnect.tsx";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Breadcrumb from "@codegouvfr/react-dsfr/Breadcrumb";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import { createFileRoute } from "@tanstack/react-router";
import React, { useState } from "react";

const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const URL_CGU = "/conditions-generales-d-utilisation";

type FormulaireCreationIdentifiantsProps = {
  profil: ProfilCreationCompte;
  labelEmail: string;
  onDemandeFranceConnect?: () => void;
};

/**
 * Ne collecte que les identifiants de connexion (email + mot de passe) : la création de compte est
 * volontairement dissociée de la récolte des informations personnelles (civilité, nom, prénom,
 * téléphone, et pour un avocat barreau/CNBF), qui fera l'objet d'un écran ultérieur (autre ticket).
 * En attendant cet écran, l'identifiant et le profil sont gardés en session (le mot de passe, lui,
 * n'est jamais persisté) et un message temporaire s'affiche à la place du formulaire.
 */
function FormulaireCreationIdentifiants({ profil, labelEmail, onDemandeFranceConnect }: FormulaireCreationIdentifiantsProps) {
  const [identifiant, setIdentifiant] = useState("");
  const [motDePasse, setMotDePasse] = useState("");
  const [confirmation, setConfirmation] = useState("");
  const [cguOk, setCguOk] = useState(false);
  const [erreur, setErreur] = useState<string | undefined>(undefined);
  const [enregistre, setEnregistre] = useState(false);

  if (enregistre) {
    return (
      <Alert
        severity="success"
        title="Vos identifiants sont enregistrés"
        description="La suite du parcours pour compléter votre profil sera bientôt disponible."
      />
    );
  }

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (!REGEX_EMAIL.test(identifiant)) {
      setErreur("L'adresse email n'est pas valide");
      return;
    }

    const erreurMotDePasse = motDePasseValide(motDePasse);
    if (erreurMotDePasse) {
      setErreur(erreurMotDePasse);
      return;
    }
    if (motDePasse !== confirmation) {
      setErreur("Les deux mots de passe doivent être identiques");
      return;
    }
    if (!cguOk) {
      setErreur("Vous devez accepter les conditions générales d'utilisation");
      return;
    }

    setErreur(undefined);
    saveCreationCompte({ profil, identifiant });
    setEnregistre(true);
  };

  return (
    <>
      <form onSubmit={onSubmit}>
        <Input
          label={labelEmail}
          className="fr-mb-3w"
          nativeInputProps={{
            type: "email",
            autoComplete: "email",
            value: identifiant,
            onChange: (e) => setIdentifiant(e.target.value),
          }}
        />

        <CreationDeCompte
          motDePasse={motDePasse}
          confirmation={confirmation}
          cguOk={cguOk}
          cguUrl={URL_CGU}
          onMotDePasseChange={setMotDePasse}
          onConfirmationChange={setConfirmation}
          onCguOkChange={setCguOk}
          erreur={erreur}
        />
      </form>

      {onDemandeFranceConnect && (
        <>
          <p className="fr-hr-or fr-my-3w">ou</p>
          <TuileFranceConnect
            detail="Vous êtes résident et/ou citoyen français."
            onClick={onDemandeFranceConnect}
          />
        </>
      )}
    </>
  );
}

function DysfonctionnementAccueilPage() {
  const [profil, setProfil] = useState<ProfilCreationCompte | undefined>("particulier");
  const [mode, setMode] = useState<"inscription" | "connexion">("inscription");
  const [afficherFranceConnect, setAfficherFranceConnect] = useState(false);

  return (
    <Layout>

      <h1>Mon Indemnisation Justice</h1>

      <div className="fr-grid-row fr-grid-row--center">
        <div className="fr-col-lg-10 fr-col-12">
          <section className="fr-p-4w fr-border-default--grey">

            <div className="fr-grid-row fr-grid-row--center">
              <div className="fr-col-lg-6 fr-col-10">
                {afficherFranceConnect ? (
                  <EcranFranceConnect
                    onRetour={() => setAfficherFranceConnect(false)}
                    bouton={<LienFranceConnect action="S’inscrire avec" />}
                  />
                ) : (
                  <>
                    <h2 className="fr-h4">{mode === "inscription" ? "Création de compte" : "Connexion à Mon indemnisation justice"}</h2>

                    <div className="fr-mb-3w fr-grid-row fr-grid-row--middle">
                      <p className="fr-mb-0 fr-mr-2w">Je suis :</p>
                      <RadioButtons
                        orientation="horizontal"
                        legend="Je suis :"
                        classes={{ legend: "fr-sr-only", root: "fr-mb-0 mij-fieldset-legende-externe" }}
                        options={[
                          {
                            label: "Un particulier",
                            nativeInputProps: {
                              checked: profil === "particulier",
                              onChange: () => setProfil("particulier"),
                            },
                          },
                          {
                            label: "Un avocat",
                            nativeInputProps: {
                              checked: profil === "avocat",
                              onChange: () => setProfil("avocat"),
                            },
                          },
                        ]}
                      />
                    </div>

                    {profil && mode === "connexion" && (
                      <FormulaireConnexion
                        labelEmail={profil === "avocat" ? "Adresse email professionnelle" : "Adresse email"}
                      />
                    )}

                    {profil === "particulier" && mode === "inscription" && (
                      <FormulaireCreationIdentifiants
                        profil="particulier"
                        labelEmail="Adresse email"
                        onDemandeFranceConnect={() => setAfficherFranceConnect(true)}
                      />
                    )}

                    {profil === "avocat" && mode === "inscription" && (
                      <FormulaireCreationIdentifiants profil="avocat" labelEmail="Adresse email professionnelle" />
                    )}
                  </>
                )}
              </div>
            </div>

          </section>

          {profil && !afficherFranceConnect && (
            <div className="fr-px-4w fr-mt-3w">
              <div className="fr-grid-row fr-grid-row--center">
                <div className="fr-col-lg-6 fr-col-10">
                  <button
                    type="button"
                    className="fr-btn fr-btn--secondary mij-bouton-pleine-largeur"
                    onClick={() => setMode(mode === "inscription" ? "connexion" : "inscription")}
                  >
                    {mode === "inscription" ? "J'ai déjà un compte" : "Je n'ai pas encore de compte"}
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </Layout>
  );
}

export const Route = createFileRoute("/dysfonctionnement/")({
  component: DysfonctionnementAccueilPage,
});
