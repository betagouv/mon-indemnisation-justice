import React, { useState, useEffect, useContext } from "react";

import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from "@/apps/requerant/dossier/components/PieceJointe/PieceJointe.tsx";
import { default as RecapitulatifBrisPorte } from "./BrisPorte/Recapitulatif.tsx";
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import BrisPorte from "@/apps/requerant/dossier/components/BrisPorte.tsx";
import User from "./User.tsx";
import {
  DossierContext,
  PatchDossierContext,
} from "@/apps/requerant/dossier/contexts/DossierContext.ts";

const BrisPortePanel = function () {
  const dossier = useContext(DossierContext);
  const patchDossier = useContext(PatchDossierContext);

  const sections = [
    "Données personnelles",
    "Informations relatives au bris de porte",
    "Documents à joindre obligatoirement à votre demande",
    "Vérification et soumission de votre demande",
  ];
  const gotoFirstSection = () => gotoSection(0);
  const gotoSecondSection = () => gotoSection(1);
  const gotoThirdSection = () => gotoSection(2);
  const gotoSection = (index) => {
    setStep(index);
  };
  const [step, setStep] = useState(dossier.dateDeclaration ? 3 : 0);
  const [title, setTitle] = useState("");
  const [nextTitle, setNextTitle] = useState("");

  function incrementStep() {
    setStep(step + 1);
    gotoStepper();
  }
  function decrementStep() {
    setStep(step - 1);
    gotoStepper();
  }
  function getCurrentStep() {
    return step + 1;
  }
  function gotoStepper() {
    document.getElementById("pr-case_stepper")?.focus();
  }

  useEffect(() => {
    setTitle(sections[step]);
    if (sections[step + 1]) setNextTitle(sections[step + 1]);
  }, [step]);

  return (
    <>
      <section tabIndex="0" className="pr-case_stepper" id="pr-case_stepper">
        <Stepper
          currentStep={getCurrentStep()}
          nextTitle={nextTitle}
          stepCount={sections.length}
          title={title}
        />
      </section>
      {step < 3 && (
        <p className="fr-my-4w">
          Sauf mention contraire, tous les champs sont obligatoires
        </p>
      )}
      {step === 0 && (
        <section className="pr-case_form fr-mb-4w">
          <User />
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12">
              <Button onClick={incrementStep}>
                Valider et passer à l'étape suivante
              </Button>
            </div>
          </div>
        </section>
      )}
      {step === 1 && (
        <>
          <BrisPorte />
          <div className="fr-col-12">
            <ul className="fr-btns-group fr-btns-group--inline-sm">
              <li>
                <Button onClick={incrementStep}>
                  Valider et passer à l'étape suivante
                </Button>
              </li>
              <li>
                <Button onClick={decrementStep} priority="secondary">
                  Revenir à l'étape précédente
                </Button>
              </li>
            </ul>
          </div>
        </>
      )}
      {step === 2 && (
        <>
          <a name="pieces"></a>
          <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
            <div className="fr-col-12">
              <section className="pr-form-section">
                <Document
                  documents={dossier.documents.attestation_information}
                  libelle="Attestation complétée par les forces de l'ordre"
                  type={"attestation_information"}
                  onRemoved={(document) => {
                    patchDossier({
                      documents: {
                        attestation_information:
                          dossier.documents.attestation_information.filter(
                            (d) => d.id !== document.id,
                          ),
                      },
                      patch: false,
                    });
                  }}
                  onUploaded={(document) =>
                    patchDossier({
                      documents: {
                        attestation_information:
                          dossier.documents.attestation_information.concat([
                            document,
                          ]),
                      },
                      patch: false,
                    })
                  }
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section">
                <Document
                  documents={dossier.documents.photo_prejudice}
                  libelle="Photos de la porte endommagée"
                  type={"photo_prejudice"}
                  onRemoved={(document) =>
                    patchDossier({
                      documents: {
                        photo_prejudice:
                          dossier.documents.photo_prejudice.filter(
                            (d) => d.id !== document.id,
                          ),
                      },
                      patch: false,
                    })
                  }
                  onUploaded={(document) =>
                    patchDossier({
                      documents: {
                        photo_prejudice:
                          dossier.documents.photo_prejudice.concat([document]),
                      },
                      patch: false,
                    })
                  }
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section">
                <Document
                  documents={dossier.documents.carte_identite}
                  libelle="Copie de votre pièce d'identité recto-verso"
                  type={"carte_identite"}
                  onRemoved={(document) =>
                    patchDossier({
                      documents: {
                        carte_identite: dossier.documents.carte_identite.filter(
                          (d) => d.id === document.id,
                        ),
                      },
                      patch: false,
                    })
                  }
                  onUploaded={(document) =>
                    patchDossier({
                      documents: {
                        carte_identite: dossier.documents.carte_identite.concat(
                          [document],
                        ),
                      },
                      patch: false,
                    })
                  }
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section">
                <Document
                  documents={dossier.documents.preuve_paiement_facture}
                  libelle="Facture acquittée attestant de la réalité des travaux de remise en état à l'identique "
                  type={"preuve_paiement_facture"}
                  onRemoved={(document) =>
                    patchDossier({
                      documents: {
                        preuve_paiement_facture:
                          dossier.documents.preuve_paiement_facture.filter(
                            (d) => d.id === document.id,
                          ),
                      },
                      patch: false,
                    })
                  }
                  onUploaded={(document) =>
                    patchDossier({
                      documents: {
                        preuve_paiement_facture:
                          dossier.documents.preuve_paiement_facture.concat([
                            document,
                          ]),
                      },
                      patch: false,
                    })
                  }
                />
              </section>
            </div>
            <div className="fr-col-12">
              <section className="pr-form-section">
                <Document
                  documents={dossier.documents.rib}
                  libelle={
                    dossier.requerant.isPersonneMorale
                      ? "Relevé d'identité bancaire de votre société"
                      : "Votre relevé d'identité bancaire"
                  }
                  type={"rib"}
                  onRemoved={(document) =>
                    patchDossier({
                      documents: {
                        rib: dossier.documents.rib.filter(
                          (d) => d.id === document.id,
                        ),
                      },
                      patch: false,
                    })
                  }
                  onUploaded={(document) =>
                    patchDossier({
                      documents: {
                        rib: dossier.documents.rib.concat([document]),
                      },
                      patch: false,
                    })
                  }
                />
              </section>
            </div>
            {/* Est propriétaire */}
            {dossier.requerant.personnePhysique.qualiteRequerant === "PRO" && (
              <div className="fr-col-12">
                <section className="pr-form-section">
                  <Document
                    documents={dossier.documents.titre_propriete}
                    libelle="Titre de propriété"
                    type={"titre_propriete"}
                    onRemoved={(document) =>
                      patchDossier({
                        documents: {
                          titre_propriete:
                            dossier.documents.titre_propriete.filter(
                              (d) => d.id === document.id,
                            ),
                        },
                        patch: false,
                      })
                    }
                    onUploaded={(document) =>
                      patchDossier({
                        documents: {
                          titre_propriete:
                            dossier.documents.titre_propriete.concat([
                              document,
                            ]),
                        },
                        patch: false,
                      })
                    }
                  />
                </section>
              </div>
            )}
            {/* Est locataire */}
            {dossier.requerant.personnePhysique.qualiteRequerant === "LOC" && (
              <div className="fr-col-12">
                <section className="pr-form-section">
                  <Document
                    documents={dossier.documents.contrat_location}
                    libelle={"Contrat de location"}
                    type={"contrat_location"}
                    onRemoved={(document) =>
                      patchDossier({
                        documents: {
                          contrat_location:
                            dossier.documents.contrat_location.filter(
                              (d) => d.id === document.id,
                            ),
                        },
                        patch: false,
                      })
                    }
                    onUploaded={(document) =>
                      patchDossier({
                        documents: {
                          contrat_location:
                            dossier.documents.contrat_location.concat([
                              document,
                            ]),
                        },
                        patch: false,
                      })
                    }
                  />
                </section>
              </div>
            )}
            {/* Est locataire */}
            {dossier.requerant.personnePhysique.qualiteRequerant === "LOC" && (
              <div className="fr-col-12">
                <section className="pr-form-section">
                  <Document
                    documents={dossier.documents.non_prise_en_charge_bailleur}
                    libelle={
                      "Attestation de non prise en charge par le bailleur"
                    }
                    type={"non_prise_en_charge_bailleur"}
                    onRemoved={(document) =>
                      patchDossier({
                        documents: {
                          non_prise_en_charge_bailleur:
                            dossier.documents.non_prise_en_charge_bailleur.filter(
                              (d) => d.id === document.id,
                            ),
                        },
                        patch: false,
                      })
                    }
                    onUploaded={(document) =>
                      patchDossier({
                        documents: {
                          non_prise_en_charge_bailleur:
                            dossier.documents.non_prise_en_charge_bailleur.concat(
                              [document],
                            ),
                        },
                        patch: false,
                      })
                    }
                  />
                </section>
              </div>
            )}
            <div className="fr-col-12">
              <section className="pr-form-section">
                <Document
                  documents={dossier.documents.non_prise_en_charge_assurance}
                  libelle={
                    "Attestation de non prise en charge par l'assurance habitation"
                  }
                  type={"non_prise_en_charge_assurance"}
                  onRemoved={(document) =>
                    patchDossier({
                      documents: {
                        non_prise_en_charge_assurance:
                          dossier.documents.non_prise_en_charge_assurance.filter(
                            (d) => d.id === document.id,
                          ),
                      },
                      patch: false,
                    })
                  }
                  onUploaded={(document) =>
                    patchDossier({
                      documents: {
                        non_prise_en_charge_assurance:
                          dossier.documents.non_prise_en_charge_assurance.concat(
                            [document],
                          ),
                      },
                      patch: false,
                    })
                  }
                />
              </section>
            </div>
            <div className="fr-col-12">
              <ul className="fr-btns-group fr-btns-group--inline-sm">
                <li>
                  <Button onClick={incrementStep}>
                    Valider et passer à l'étape suivante
                  </Button>
                </li>
                <li>
                  <Button onClick={decrementStep} priority="secondary">
                    Revenir à l'étape précédente
                  </Button>
                </li>
              </ul>
            </div>
          </div>
        </>
      )}
      {step === 3 && (
        <>
          <RecapitulatifBrisPorte
            gotoFirstSection={gotoFirstSection}
            gotoSecondSection={gotoSecondSection}
          />
          <div className="fr-col-12">
            <ul className="fr-btns-group fr-btns-group--inline-sm">
              <li>
                <Button
                  linkProps={{
                    // Route: app_requerant_update_statut_to_constitue
                    href: `/requerant/bris-de-porte/passage-a-l-etat-constitue/${dossier.id}`,
                  }}
                >
                  {dossier.dateDeclaration
                    ? "Mettre à jour mon dossier"
                    : "Je déclare mon bris de porte"}
                </Button>
              </li>
              <li>
                <Button onClick={decrementStep} priority="secondary">
                  Revenir à l'étape précédente
                </Button>
              </li>
            </ul>
          </div>
        </>
      )}
    </>
  );
};

export default BrisPortePanel;
