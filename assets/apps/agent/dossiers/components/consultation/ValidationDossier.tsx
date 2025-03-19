import { Courrier, Document, DocumentType } from "@/apps/agent/dossiers/models";
import { DossierDetail } from "@/apps/agent/dossiers/models/Dossier";
import { plainToInstance } from "class-transformer";
import { observer } from "mobx-react-lite";
import React, { useRef, useState } from "react";
import ReactQuill from "react-quill-new";

export const ValidationDossier = observer(function ValidationDossierComponent({
  dossier,
  onEdite,
  onSigne,
}: {
  dossier: DossierDetail;
  onEdite: null | (() => void);
  onSigne: null | (() => void);
}) {
  //
  const refModale = useRef(null);

  // Mémorise la décision en cours
  const [decision, decider]: [
    boolean | null,
    (decision: boolean | null) => void,
  ] = useState(null);

  const ouvrirModale = (decision: boolean) => {
    decider(decision);
    refModale.current?.classList.add("fr-modal--opened");
  };

  const fermerModale = () => {
    refModale.current?.classList.remove("fr-modal--opened");
  };

  // Mémorise le montant de l'indemnisation
  const [montantIndemnisation, setMontantIndemnisation]: [
    number,
    (montant: number) => void,
  ] = useState(dossier.montantIndemnisation);

  // Mémorise le montant de l'indemnisation lu dans le courrier
  const [montantIndemnisationLu, setMontantIndemnisationLu]: [
    number | null,
    (montant: number | null) => void,
  ] = useState(null);

  // Corps du courrier
  const [courrier, setCourrier]: [string | null, (courrier: string) => void] =
    useState(dossier.corpsCourrier);

  // Fichier signé à téléverser
  const [fichierSigne, setFichierSigne]: [
    File | null,
    (fichierSigne: File) => void,
  ] = useState(null);

  // Fichier signé à téléverser
  const [estFichierSigne, marquerFichierSigne]: [
    boolean,
    (estFichierSigne: boolean) => void,
  ] = useState(false);

  const estTailleFichierOk = (fichier?: File) =>
    fichier && fichier.size < 10 * 1024 * 1024;
  const estTypeFichierOk = (fichier?: File) =>
    fichier && ["application/pdf"].includes(fichier.type);

  // Indique si la sauvegarde du rédacteur attribué est en cours (le cas échéant affiche un message explicit et bloque les boutons)
  const [sauvegarderEnCours, setSauvegarderEnCours]: [
    boolean,
    (mode: boolean) => void,
  ] = useState(false);

  // Actions

  const detecterMontantIndemnisation = (texte: string) => {
    const montantBrut = texte
      .match(new RegExp("(\\s?\\d)+(,[0-9]{1,2})\\s*EUR", "g"))
      ?.at(0);

    if (montantBrut) {
      setMontantIndemnisationLu(
        parseFloat(
          montantBrut
            .replace(" ", "")
            .replace("EUR", "")
            .trim()
            .replace(",", "."),
        ),
      );
    }
  };

  const editerCourrier = async (motif?: string) => {
    setSauvegarderEnCours(true);

    const response = await fetch(
      `/agent/redacteur/dossier/${dossier.id}/courrier/editer.json`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          montantIndemnisation,
          corpsCourrier: courrier,
        }),
      },
    );

    if (response.ok) {
      const data = await response.json();
      dossier.setCourrier(plainToInstance(Courrier, data.courrier));
      dossier.viderDocumentParType(DocumentType.TYPE_COURRIER_MINISTERE);
    }

    fermerModale();
    setSauvegarderEnCours(false);
    marquerFichierSigne(false);
    setFichierSigne(null);
    decider(null);
    // Déclencher le _hook_ onEdite s'il est défini
    onEdite?.();
  };

  const signerCourrier = async (fichier: File) => {
    setSauvegarderEnCours(true);

    try {
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/courrier/signer.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
          body: (() => {
            const data = new FormData();
            data.append("fichierSigne", fichier);

            return data;
          })(),
        },
      );

      if (response.ok) {
        const data = await response.json();
        if (data.documents.courrier_ministere?.length) {
          dossier.addDocument(
            plainToInstance(Document, data.documents.courrier_ministere?.at(0)),
          );
        }
      }
    } catch (e) {
      console.error(e);
    } finally {
      fermerModale();
      setSauvegarderEnCours(false);
      marquerFichierSigne(true);
      decider(null);
      // Déclencher le _hook_ onSigne s'il est défini
      onSigne?.();
    }
  };

  const envoyerAuRequerant = async () => {
    setSauvegarderEnCours(true);

    try {
      const response = await fetch(
        `/agent/redacteur/dossier/${dossier.id}/envoyer.json`,
        {
          method: "POST",
          headers: {
            Accept: "application/json",
          },
        },
      );

      if (response.ok) {
        const data = await response.json();
        dossier.changerEtat(data.etat);
      }
    } catch (e) {
      console.error(e);
    } finally {
      setSauvegarderEnCours(false);
      marquerFichierSigne(true);
      decider(null);
      // Déclencher le _hook_ onSigne s'il est défini
      onSigne?.();
    }
  };

  return (
    <>
      <div>
        <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
          <li>
            <button
              className="fr-btn fr-btn--sm fr-btn--secondary"
              type="button"
              onClick={() => {
                ouvrirModale(false);
              }}
            >
              Éditer la proposition d'indemnisation
            </button>
          </li>
          <li>
            <button
              className="fr-btn fr-btn--sm fr-btn--primary"
              type="button"
              onClick={() => {
                ouvrirModale(true);
              }}
              disabled={estFichierSigne}
            >
              Signer le courrier
            </button>
          </li>
          <li>
            <button
              className="fr-btn fr-btn--sm fr-btn--primary"
              type="button"
              disabled={!estFichierSigne}
              onClick={() => {
                ouvrirModale(true);
              }}
            >
              Envoyer au requérant
            </button>
          </li>
        </ul>
      </div>

      <dialog
        aria-labelledby="modale-dossier-decision-titre"
        role="dialog"
        id="modale-dossier-decision"
        ref={refModale}
        data-fr-concealing-backdrop="true"
        className="fr-modal"
      >
        <div className="fr-container fr-container--fluid fr-container-md">
          <div className="fr-grid-row fr-grid-row--center">
            <div className={`fr-col-12 fr-col-lg-${decision ? 6 : 10}`}>
              <div className="fr-modal__body">
                <div className="fr-modal__header">
                  <button
                    className="fr-btn--close fr-btn"
                    title="Fermer la fenêtre modale"
                    aria-controls="modale-dossier-decision"
                    onClick={() => fermerModale()}
                  >
                    Fermer
                  </button>
                </div>
                {
                  // Édition du corps du courrier
                  !decision && (
                    <div className="fr-modal__content">
                      <h1
                        id="modale-dossier-decision-titre"
                        className="fr-modal__title"
                      >
                        <span className="fr-icon-edit-box-line fr-icon--lg fr-mr-1w"></span>
                        Éditer la proposition d'indemnisation
                      </h1>
                      {dossier.estAccepte() && (
                        <div
                          className="fr-input-group fr-my-2w fr-grid-row"
                          style={{ alignItems: "center" }}
                        >
                          <label
                            className="fr-label fr-col-6"
                            htmlFor="dossier-decision-acceptation-indemnisation-champs"
                          >
                            Montant de l'indemnisation
                          </label>
                          <div className="fr-input-wrap fr-icon-money-euro-circle-line fr-col-6">
                            <input
                              className="fr-input"
                              defaultValue={montantIndemnisation}
                              onInput={(e: InputEvent) => {
                                const input = e.target as HTMLInputElement;

                                setMontantIndemnisation(
                                  input.value?.match(/^\d+(.\d{0,2})?$/)
                                    ? parseFloat(input.value?.replace(",", "."))
                                    : null,
                                );

                                if (dossier.estAccepte()) {
                                  detecterMontantIndemnisation(courrier);
                                }
                              }}
                              aria-describedby="dossier-decision-acceptation-indemnisation-messages"
                              id="dossier-decision-acceptation-indemnisation-champs"
                              type="number"
                              step=".01"
                              inputMode="numeric"
                            />
                          </div>

                          {!montantIndemnisation && (
                            <div
                              className="fr-messages-group fr-message--error fr-my-1w"
                              id="dossier-decision-acceptation-indemnisation-messages"
                              aria-live="polite"
                            >
                              <span>
                                Vous devez définir un montant d'indemnisation
                              </span>
                            </div>
                          )}
                        </div>
                      )}
                      <div className="fr-input-group fr-my-2w">
                        <ReactQuill
                          theme="snow"
                          value={courrier}
                          onChange={(value) => {
                            setCourrier(value);
                            if (dossier.estAccepte()) {
                              detecterMontantIndemnisation(value);
                            }
                          }}
                          readOnly={sauvegarderEnCours}
                        />
                        <div />
                      </div>

                      {dossier.estAccepte() &&
                        montantIndemnisationLu &&
                        montantIndemnisation !== montantIndemnisationLu && (
                          <div className="fr-alert fr-alert--warning">
                            <h3 className="fr-alert__title">
                              Attention : risque d'ambigüité sur le montant de
                              l'indemnisation
                            </h3>
                            <p>
                              Vous indiquez indemniser à hauteur de{" "}
                              <span className={"fr-text--bold"}>
                                {montantIndemnisation} €
                              </span>
                              , pourtant le courrier mentionne un montant
                              <i> en chiffres</i> de{" "}
                              <span className={"fr-text--bold"}>
                                {montantIndemnisationLu} €
                              </span>
                              .
                            </p>

                            <p>
                              Puisque la valeur déclarée dans le champs "Montant
                              de l'indemnisation" sera également mentionnée sur
                              le formulaire de déclaration d'acceptation, il y a
                              un risque d'ambigüité pour le requérant.
                            </p>

                            <p>
                              Veillez donc à bien accorder les montants dans le
                              courrier (en chiffres ainsi qu'en toutes lettres).
                            </p>
                          </div>
                        )}

                      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                            type="button"
                            onClick={() => fermerModale()}
                            disabled={sauvegarderEnCours}
                          >
                            {sauvegarderEnCours ? (
                              <i>Sauvegarde en cours ...</i>
                            ) : (
                              <>Annuler</>
                            )}
                          </button>
                        </li>

                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--primary"
                            type="button"
                            onClick={() => editerCourrier()}
                            disabled={sauvegarderEnCours}
                          >
                            Ré-éditer le courrier
                          </button>
                        </li>
                      </ul>
                    </div>
                  )
                }
                {
                  // Téléversement, pour signature, du courrier
                  decision && !estFichierSigne && (
                    <div className="fr-modal__content">
                      <h1
                        id="modale-dossier-decision-titre"
                        className="fr-modal__title"
                      >
                        <span className="fr-icon-edit-box-line fr-icon--lg fr-mr-1w"></span>
                        Signer le courrier
                      </h1>

                      <div className="fr-input-group fr-mb-3w">
                        <a
                          className="fr-link fr-link--download"
                          download={`Lettre décision dossier ${dossier.reference}`}
                          href={dossier.courrier.url}
                        >
                          Télécharger le courrier
                          <span className="fr-link__detail">PDF</span>
                        </a>
                      </div>

                      <div className="fr-upload-group">
                        <label className="fr-label" htmlFor="file-upload">
                          Téléverser le fichier pour signature
                          <span className="fr-hint-text">
                            <span
                              className={`${!estTailleFichierOk(fichierSigne) ? "fr-text-default--error" : ""}`}
                            >
                              Taille maximale : 10 Mo.&nbsp;
                            </span>
                            <span
                              className={`${!estTypeFichierOk(fichierSigne) ? "fr-text-default--error" : ""}`}
                            >
                              Format pdf uniquement.&nbsp;
                            </span>
                          </span>
                        </label>{" "}
                        <input
                          className="fr-upload"
                          type="file"
                          id="file-upload"
                          name="file-upload"
                          accept="application/pdf"
                          onChange={(e) => {
                            setFichierSigne(e.target.files[0] ?? null);
                          }}
                        />
                      </div>
                      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                            type="button"
                            onClick={() => fermerModale()}
                            disabled={sauvegarderEnCours}
                          >
                            {sauvegarderEnCours ? (
                              <i>Sauvegarde en cours ...</i>
                            ) : (
                              <>Annuler</>
                            )}
                          </button>
                        </li>
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--secondary"
                            type="button"
                            onClick={() => decider(false)}
                            disabled={sauvegarderEnCours}
                          >
                            Éditer le courrier
                          </button>
                        </li>
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--primary"
                            type="button"
                            disabled={
                              !fichierSigne ||
                              !estTypeFichierOk(fichierSigne) ||
                              !estTailleFichierOk(fichierSigne) ||
                              sauvegarderEnCours
                            }
                            onClick={() => signerCourrier(fichierSigne)}
                          >
                            Téléverser et signer
                          </button>
                        </li>
                      </ul>
                    </div>
                  )
                }
                {
                  // Envoi au requérant
                  decision && estFichierSigne && (
                    <div className="fr-modal__content">
                      <h1
                        id="modale-dossier-decision-titre"
                        className="fr-modal__title"
                      >
                        <span className="fr-icon-edit-box-line fr-icon--lg fr-mr-1w"></span>
                        Envoyer le courrier au requérant
                      </h1>

                      <div className="fr-alert fr-alert--info">
                        <h3 className="fr-alert__title">Envoi imminent</h3>
                        <p>
                          Vous vous apprêtez à faire part de votre décision au
                          requérant via l'envoi du courrier dûment signé.
                        </p>
                        <p>
                          Cette action est définitive: une fois le courrier
                          transmis, vous n'aurez plus la possibilité d'éditer
                          votre réponse.
                        </p>
                        <p>
                          Aussi,{" "}
                          <span className="fr-text--bold">
                            veillez à bien relire le document
                          </span>{" "}
                          afin de vous assurer que tout est conforme.
                        </p>
                      </div>

                      <ul className="fr-btns-group fr-btns-group--sm fr-btns-group--inline fr-btns-group--right fr-mt-3w">
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--tertiary-no-outline"
                            type="button"
                            onClick={() => fermerModale()}
                            disabled={sauvegarderEnCours}
                          >
                            {sauvegarderEnCours ? (
                              <i>Sauvegarde en cours ...</i>
                            ) : (
                              <>Annuler</>
                            )}
                          </button>
                        </li>
                        <li>
                          <button
                            className="fr-btn fr-btn--sm fr-btn--primary"
                            type="button"
                            disabled={sauvegarderEnCours}
                            onClick={() => envoyerAuRequerant()}
                          >
                            Envoyer au requérant
                          </button>
                        </li>
                      </ul>
                    </div>
                  )
                }
              </div>
            </div>
          </div>
        </div>
      </dialog>
    </>
  );
});
