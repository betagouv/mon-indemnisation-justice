import "reflect-metadata";
import {
  Document,
  DocumentType,
  DossierDetail,
} from "@/apps/agent/dossiers/models";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import { Upload } from "@codegouvfr/react-dsfr/Upload";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { useRef, useState } from "react";
import ReactDOM from "react-dom/client";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useIsModalOpen } from "@codegouvfr/react-dsfr/Modal/useIsModalOpen";
import { disableReactDevTools } from "@/apps/requerant/dossier/services/devtools.js";

startReactDsfr({ defaultColorScheme: "system" });

// En développement, vider la console après chaque action de HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.on("vite:beforeUpdate", () => console.clear());
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

// En production, désactivation de React devtools
if (import.meta.env.PROD) {
  disableReactDevTools();
}

const args = JSON.parse(document.getElementById("react-arguments").textContent);

const dossier = plainToInstance(DossierDetail, args.dossier, {
  enableImplicitConversion: true,
});

const signatureModal = createModal({
  id: "modale-signature-dossier",
  isOpenedByDefault: false,
});

const ConsulterDecisionApp = observer(function ConsulterDecisionApp({
  dossier,
}: {
  dossier: DossierDetail;
}) {
  // Références vers les conteneurs DOM de chacune des étapes du formulaire de signature
  const refEtapes = [useRef(null), useRef(null), useRef(null)];
  // Numéro de l'étape active sur le formulaire de signature
  const [etape, setEtape] = useState(0);

  // Fichier signé à téléverser
  const [fichierSigne, setFichierSigne]: [
    File | null,
    (fichierSigne: File) => void,
  ] = useState(null);

  const estTailleFichierOk = () =>
    fichierSigne && fichierSigne.size < 10 * 1024 * 1024;
  const estTypeFichierOk = () =>
    fichierSigne && ["application/pdf"].includes(fichierSigne.type);

  // Indique si l'envoi du fichier, via l'API, est en cours
  const [sauvegarderEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  const signerCourrier = async () => {
    setSauvegarderEnCours(true);

    try {
      const response = await fetch(
        `/requerant/bris-de-porte/${dossier.id}/accepter-la-decision.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: (() => {
            const data = new FormData();
            data.append("fichierSigne", fichierSigne);

            return data;
          })(),
        },
      );

      if (response.ok) {
        const data = await response.json();
        if (data.documents.courrier_requerant?.length) {
          dossier.addDocument(
            plainToInstance(Document, data.documents.courrier_requerant?.at(0)),
          );
        }
        if (data.etat) {
          dossier.changerEtat(data.etat);
        }
      }
    } catch (e) {
      console.error(e);
    } finally {
      signatureModal.close();
      setSauvegarderEnCours(false);
    }
  };

  return (
    <div className="fr-container">
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h1>Votre demande d'indemnisation</h1>

          <p>
            La demande d'indemnisation que vous avez initié le{" "}
            {dossier.dateDepot.toLocaleString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
            })}
            , concernant un bris de porte survenu le{" "}
            {dossier.dateOperation.toLocaleString("fr-FR", {
              weekday: "long",
              day: "numeric",
              month: "long",
              year: "numeric",
            })}{" "}
            au logement{" "}
            {dossier.adresse.estRenseignee() ? (
              <>
                situé{" "}
                {dossier.adresse.libelle() /*13 rue des Oliviers 49000 ANGERS*/}
              </>
            ) : (
              ""
            )}{" "}
            dont vous êtes{" "}
            {dossier.testEligibilite.estProprietaire
              ? "propriétaire"
              : "locataire"}
            , a été {dossier.estAccepte() ? "acceptée" : "refusée"}.
          </p>

          <p>
            Vous trouverez ci-dessous le courrier expliquant les motivations de
            cette décision.
          </p>

          {dossier.estAccepte() && (
            <p>
              Le montant de l'indemnisation qui vous est proposé est de{" "}
              <span className="fr-text--bold">
                {dossier.montantIndemnisation.toLocaleString()} €
              </span>
              . Pour accepter la proposition et déclencher le versement de cette
              somme, il vous faut signer le document et nous le retourner en
              cliquant sur le bouton "Accepter la proposition".
            </p>
          )}
        </div>

        {dossier.estAccepte() &&
          (dossier.estAccepteRequerant() ? (
            <>
              <div className="fr-col-12 fr-my-2w">
                <Alert
                  severity="info"
                  title="Indemnisation acceptée"
                  description="Vous avez accepté la proposition d'indemnisation. Le virement du montant correspondant vers vore compte bancaire devrait être exécuté prochainement"
                  closable={false}
                />
              </div>
            </>
          ) : (
            <>
              <div className="fr-col-lg-3 fr-col-offset-lg-9">
                <ul className="fr-btns-group fr-btns-group--right">
                  <li>
                    <button
                      className="fr-btn"
                      onClick={() => signatureModal.open()}
                    >
                      Accepter la proposition
                    </button>
                  </li>
                </ul>
              </div>
              <signatureModal.Component
                title="Signature du dossier"
                iconId={"fr-icon-ball-pen-line"}
                children={null}
                size={"large"}
              >
                <Stepper
                  currentStep={etape + 1}
                  stepCount={refEtapes.length}
                  title={refEtapes
                    .at(etape)
                    .current?.getAttribute("data-titre")}
                />

                <div
                  hidden={etape != 0}
                  ref={refEtapes.at(0)}
                  data-titre="Récupérer le document"
                >
                  <p>
                    Pour accepter la proposition d'indemnisation, vous allez
                    devoir signer <i>électroniquement</i> la déclaration
                    d'acceptation, figurant en annexe du courrier. Pour cela
                    nous recommandons d'utiliser un ordinateur.
                  </p>

                  <p>
                    La première étape consiste à enregistrer le document sur
                    votre disque-dur en cliquant sur le bouton "Télécharger le
                    courrier" ci-dessous, avant de pouvoir passer à l'étape
                    suivante.
                  </p>

                  <div className="fr-input-group fr-mb-3w">
                    <a
                      className="fr-link fr-link--download"
                      download={`Lettre décision dossier ${dossier.reference}`}
                      href={
                        dossier
                          .getDocumentsType(
                            DocumentType.TYPE_COURRIER_MINISTERE,
                          )
                          .at(0)?.url
                      }
                    >
                      Télécharger le courrier
                    </a>
                  </div>

                  <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                        type="button"
                        onClick={() => {
                          signatureModal.close();
                          setEtape(0);
                        }}
                      >
                        Annuler
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--primary"
                        type="button"
                        onClick={() => setEtape(1)}
                      >
                        Étape suivante
                      </button>
                    </li>
                  </ul>
                </div>

                <div
                  hidden={etape != 1}
                  className={""}
                  ref={refEtapes.at(1)}
                  data-titre="Remplir et signer le formulaire"
                >
                  <p>
                    La déclaration d'acceptation doit être remplie avec l'ajout
                    d'informations concernant votre état civil, de vos
                    coordonnées bancaires <i>à jour</i> ainsi qu'une signature
                    manuscrite, directement sur le document PDF que vous avez
                    récupéré.
                  </p>

                  <p>
                    Il existe plusieurs logiciels qui permettent de le faire,
                    mais nous vous invitons à suivre{" "}
                    <a
                      href="https://lesbases.anct.gouv.fr/ressources/remplir-et-signer-un-fichier-pdf"
                      title="Comment remplir et signer un fichier PDF, procédure expliquée et recommandée par l'ANCT"
                    >
                      la démarche recommandée par l'ANCT
                    </a>
                    . Vous pouvez aussi utiliser{" "}
                    <a
                      href="https://www.ilovepdf.com/fr/modifier-pdf"
                      title="Modifier et signer un PDF avec le site iLovePDF"
                    >
                      la fonctionnalité "Modifier un PDF" depuis le site
                      iLovePDF
                    </a>
                    .
                  </p>

                  <p>
                    Une fois que vous avez modifié et <b>sauvegardé</b> le
                    document, passez à l'étape suivante.
                  </p>

                  <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                        type="button"
                        onClick={() => {
                          signatureModal.close();
                          setEtape(0);
                        }}
                      >
                        Annuler
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--secondary"
                        type="button"
                        onClick={() => setEtape(0)}
                      >
                        Étape précédente
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--primary"
                        type="button"
                        onClick={() => setEtape(2)}
                      >
                        Étape suivante
                      </button>
                    </li>
                  </ul>
                </div>
                <div
                  hidden={etape != 2}
                  className={""}
                  ref={refEtapes.at(2)}
                  data-titre="Transmettre le document signé"
                >
                  <p>
                    Maintenant que la déclaration est dûment remplie et signée,
                    il ne vous reste plus qu'à la transmettre au bureau du
                    précontentieux en la téléversant sur la plateforme.
                  </p>
                  <p>
                    Sélectionnez ci-dessous le fichier que vous venez d'éditer,
                    puis validez en cliquant sur le bouton "Envoyer".
                  </p>
                  <Upload
                    label="Téléverser le fichier pour accepter la proposition et être
                    indémnisé"
                    hint="Taille maximale : 10 Mo, format pdf uniquement."
                    state={
                      !fichierSigne ||
                      (estTypeFichierOk() && estTailleFichierOk())
                        ? "default"
                        : "error"
                    }
                    stateRelatedMessage={
                      !estTailleFichierOk()
                        ? "Le fichier dépasse les 10 Mo"
                        : "Le fichier n'est pas au format PDF"
                    }
                    nativeInputProps={{
                      accept: "application/pdf",
                      onChange: (e) => setFichierSigne(e.target.files[0]),
                    }}
                    multiple={false}
                  />
                  <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                        type="button"
                        disabled={sauvegarderEnCours}
                        onClick={() => {
                          signatureModal.close();
                          setEtape(0);
                        }}
                      >
                        Annuler
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--secondary"
                        type="button"
                        onClick={() => setEtape(1)}
                        disabled={sauvegarderEnCours}
                      >
                        Étape précédente
                      </button>
                    </li>
                    <li>
                      <button
                        className="fr-btn fr-btn--sm fr-btn--primary"
                        type="button"
                        onClick={() => signerCourrier()}
                        disabled={
                          sauvegarderEnCours ||
                          !fichierSigne ||
                          !estTailleFichierOk() ||
                          !estTypeFichierOk()
                        }
                      >
                        Envoyer
                      </button>
                    </li>
                  </ul>
                </div>
              </signatureModal.Component>
            </>
          ))}

        <div className="fr-col-12">
          <object
            data={
              dossier
                .getDocumentsType(
                  dossier.estAccepteRequerant()
                    ? DocumentType.TYPE_COURRIER_REQUERANT
                    : DocumentType.TYPE_COURRIER_MINISTERE,
                )
                .at(0)?.url
            }
            type="application/pdf"
            style={{ width: "100%", aspectRatio: "210 / 297" }}
          ></object>
        </div>
      </div>
    </div>
  );
});

ReactDOM.createRoot(document.getElementById("react-app")).render(
  <React.StrictMode>
    <>
      <ConsulterDecisionApp dossier={dossier} />
    </>
  </React.StrictMode>,
);
