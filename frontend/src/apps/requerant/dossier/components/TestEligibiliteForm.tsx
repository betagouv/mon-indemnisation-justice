import React, { useEffect, useMemo, useRef, useState } from "react";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { ButtonProps } from "@codegouvfr/react-dsfr/Button";
import { Input } from "@codegouvfr/react-dsfr/Input";
import CallOut from "@codegouvfr/react-dsfr/CallOut";

type LibelleAvancementTest =
  | "description"
  | "est_vise"
  | "est_hebergeant"
  | "est_proprietaire"
  | "a_contacte_assurance"
  | "a_contacte_bailleur";

const AvancementTest: LibelleAvancementTest[] = [
  "description",
  "est_vise",
  "est_hebergeant",
  "est_proprietaire",
  "a_contacte_assurance",
  "a_contacte_bailleur",
];

type TestEligibilite = {
  avancement: LibelleAvancementTest;
  description?: string;
  estVise?: boolean;
  estHebergeant?: boolean;
  estProprietaire?: boolean;
  aContacteAssurance?: boolean;
  aContacteBailleur?: boolean;
};

export const TestEligibiliteForm = ({
  token,
  estIssuAttestation,
}: {
  token: string;
  estIssuAttestation: boolean;
}) => {
  const [test, setTest] = useState<TestEligibilite>({
    avancement: "description",
  });

  const [soumissionEnCours, setSoumissionEnCours] = useState(false);

  const setDescription = (description: string) =>
    setTest({ description, avancement: "description" });

  const setEstVise = (estVise: boolean) =>
    setTest({
      description: test.description,
      estVise,
      avancement: test.estVise ? "est_vise" : "est_hebergeant",
    });

  const setEstHebergeant = (estHebergeant: boolean) =>
    setTest({
      description: test.description,
      estVise: test.estVise,
      estHebergeant,
      avancement: test.estHebergeant ? "est_hebergeant" : "est_proprietaire",
    });

  const setEstProprietaire = (estProprietaire: boolean) =>
    setTest({
      description: test.description,
      estVise: test.estVise,
      estHebergeant: test.estHebergeant,
      estProprietaire,
      avancement: "a_contacte_assurance",
    });

  const setAContacteAssurance = (aContacteAssurance: boolean) =>
    setTest({
      description: test.description,
      estVise: test.estVise,
      estHebergeant: test.estHebergeant,
      estProprietaire: test.estProprietaire,
      aContacteAssurance,
      avancement: test.estProprietaire
        ? "a_contacte_assurance"
        : "a_contacte_bailleur",
    });

  const setAContacteBailleur = (aContacteBailleur: boolean) =>
    setTest({
      description: test.description,
      estVise: test.estVise,
      estHebergeant: test.estHebergeant,
      estProprietaire: test.estProprietaire,
      aContacteAssurance: test.aContacteAssurance,
      aContacteBailleur,
      avancement: "a_contacte_bailleur",
    });

  const estQuestionVisible = (avancement: LibelleAvancementTest) =>
    AvancementTest.indexOf(test.avancement) >=
    AvancementTest.indexOf(avancement);

  const estDecide = useMemo(() => {
    if (true === test.estVise) {
      return true;
    }

    if (true === test.estHebergeant) {
      return true;
    }

    return test.estProprietaire != null && test.estProprietaire
      ? test.aContacteAssurance != null
      : test.aContacteBailleur != null;
  }, [test]);

  const refQuestionEstVise = useRef(null);
  const refQuestionEstHebergeant = useRef(null);
  const refQuestionEstProprietaire = useRef(null);
  const refQuestionAContacteAssurance = useRef(null);
  const refQuestionAContacteBailleur = useRef(null);
  const refDecision = useRef(null);

  useEffect(() => {
    if (estDecide) {
      refDecision.current?.scrollIntoView({ behavior: "smooth" });
    } else {
      if (test.avancement === "est_vise") {
        refQuestionEstVise.current?.scrollIntoView({ behavior: "smooth" });
      }

      if (test.avancement === "est_hebergeant") {
        refQuestionEstHebergeant.current?.scrollIntoView({
          behavior: "smooth",
        });
      }

      if (test.avancement === "est_proprietaire") {
        refQuestionEstProprietaire.current?.scrollIntoView({
          behavior: "smooth",
        });
      }

      if (test.avancement === "a_contacte_assurance") {
        refQuestionAContacteAssurance.current?.scrollIntoView({
          behavior: "smooth",
        });
      }
    }
  }, [test]);

  return (
    <form name="eligibilite-test-form" method="POST">
      <input type="hidden" name="_token" value={token} />
      <input
        type="hidden"
        name="estIssuAttestation"
        value={estIssuAttestation.toString()}
      />
      <Alert
        className="fr-mb-2w fr-py-1w"
        description={
          <>
            Si vous avez un compte,{" "}
            <a className="fr-link" href="/connexion">
              connectez-vous
            </a>
            .
          </>
        }
        severity="info"
        small
      />
      <CallOut title="Avant de remplir le questionnaire">
        Il est important de noter que la victime d’un dommage ne peut obtenir
        des indemnisations distinctes en réparation du même préjudice. Afin de
        garantir{" "}
        <Tooltip
          title="En vertu du principe de réparation intégrale du préjudice sans
              perte ni profit, la victime d'un dommage ne peut obtenir deux
              indemnisations distinctes en réparation du même préjudice (Cour de
              cassation, civile, Chambre civile 2, 16 décembre 2021, 19-11.294,
              Inédit ; Cour de cassation, civile, Chambre civile 2, 9 février
              2023, 21-21.217, Publié au bulletin)"
          kind="hover"
        >
          <a className="fr-link" href="#">
            le respect de ce principe
          </a>
        </Tooltip>{" "}
        et vérifier vot re éligibilité à l'indemnisation, vous devez répondre
        aux questions suivantes.
      </CallOut>

      {/* Questions */}
      {estQuestionVisible("description") && (
        <div className="pr-section-formulaire fr-my-3w">
          <h4>Contexte</h4>

          <Input
            label="Décrivez-nous l’intervention"
            textArea
            nativeTextAreaProps={{
              name: "description",
              onChange: (e) => setDescription(e.target.value),
              onFocus: () => setTest({ ...test, avancement: "description" }),
              placeholder: "Intervention survenue ce matin ...",
              rows: 10,
              cols: 50,
            }}
          />

          {test.avancement === "description" && (
            <ButtonsGroup
              className="fr-mt-2w"
              inlineLayoutWhen="always"
              alignment="right"
              isReverseOrder={true}
              buttons={[
                {
                  priority: "primary",
                  size: "small",
                  type: "button",
                  children: "Passer à la question suivante",
                  onClick: () => setTest({ ...test, avancement: "est_vise" }),
                },
              ]}
            />
          )}
        </div>
      )}
      {estQuestionVisible("est_vise") && (
        <div
          className="pr-section-formulaire fr-my-3w"
          ref={refQuestionEstVise}
        >
          <h4>
            Personne recherchée par les{" "}
            <Tooltip
              title="La police judiciaire est chargée de constater les infractions
              pénales, d'en rassembler les preuves, d'en rechercher les auteurs
              et complices afin de les interpeller et de les déférer à
              l'autorité judiciaire"
              kind="hover"
            >
              <a
                className="fr-link"
                aria-describedby="tooltip-force-de-l-ordre"
                style={{ fontSize: "1.5rem" }}
                id="link-force-de-l-ordre"
                href="#"
              >
                forces de l'ordre
              </a>
            </Tooltip>
          </h4>
          {estQuestionVisible("est_vise") && (
            <div className="fr-col-12 fr-my-2w">
              <RadioButtons
                legend="Étiez-vous la personne recherchée par les forces de l’ordre lors de leur intervention ?"
                orientation="horizontal"
                options={[
                  {
                    label: "Oui",
                    nativeInputProps: {
                      name: "estVise",
                      checked: test.estVise === true,
                      value: "true",
                      onChange: () => setEstVise(true),
                    },
                  },
                  {
                    label: "Non",
                    nativeInputProps: {
                      name: "estVise",
                      checked: test.estVise === false,
                      value: "false",
                      onChange: () => setEstVise(false),
                    },
                  },
                ]}
                state="default"
              />
            </div>
          )}

          <div
            className={`fr-col-12 fr-my-2w ${estQuestionVisible("est_hebergeant") ? "" : "fr-hidden"}`}
            ref={refQuestionEstHebergeant}
          >
            <RadioButtons
              legend="Est-ce que la personne recherchée par les forces de l'ordre réside ou est hébergée à l'adresse du logement ayant subi le bris de porte ?"
              orientation="horizontal"
              options={[
                {
                  label: "Oui",
                  nativeInputProps: {
                    name: "estHebergeant",
                    checked: test.estHebergeant === true,
                    value: "true",
                    onChange: () => setEstHebergeant(true),
                  },
                },
                {
                  label: "Non",
                  nativeInputProps: {
                    name: "estHebergeant",
                    checked: test.estHebergeant === false,
                    value: "false",
                    onChange: () => setEstHebergeant(false),
                  },
                },
              ]}
              state="default"
            />
          </div>
        </div>
      )}

      <div
        className={`pr-section-formulaire fr-my-3w ${estQuestionVisible("est_proprietaire") ? "" : "fr-hidden"}`}
        ref={refQuestionEstProprietaire}
      >
        <h4>Situation liée au logement</h4>
        <div className="fr-col-12">
          <RadioButtons
            legend="Quel est votre statut par rapport au logement ayant subi le bris de porte ?"
            orientation="horizontal"
            options={[
              {
                label: "Propriétaire",
                nativeInputProps: {
                  name: "estProprietaire",
                  checked: test.estProprietaire === true,
                  value: "true",
                  onChange: () => setEstProprietaire(true),
                },
              },
              {
                label: "Locataire",
                nativeInputProps: {
                  name: "estProprietaire",
                  checked: test.estProprietaire === false,
                  value: "false",
                  onChange: () => setEstProprietaire(false),
                },
              },
            ]}
            state="default"
          />
        </div>

        <div
          className={`fr-col-12 fr-my-2w ${estQuestionVisible("a_contacte_assurance") ? "" : "fr-hidden"}`}
          ref={refQuestionAContacteAssurance}
        >
          <RadioButtons
            legend="Avez-vous pris contact avec votre assurance et obtenu une attestation de non prise en charge du sinistre ?"
            orientation="horizontal"
            options={[
              {
                label: "Oui",
                nativeInputProps: {
                  name: "aContacteAssurance",
                  checked: test.aContacteAssurance === true,
                  value: "true",
                  onChange: () => setAContacteAssurance(true),
                },
              },
              {
                label: "Non",
                nativeInputProps: {
                  name: "aContacteAssurance",
                  checked: test.aContacteAssurance === false,
                  value: "false",
                  onChange: () => setAContacteAssurance(false),
                },
              },
            ]}
            state="default"
          />
        </div>

        {estQuestionVisible("a_contacte_bailleur") &&
          false === test.estProprietaire && (
            <div
              className="fr-col-12 fr-my-2w"
              ref={refQuestionAContacteBailleur}
            >
              <RadioButtons
                legend="Avez-vous pris contact avec votre bailleur et obtenu une attestation de non prise en charge des réparations ?"
                orientation="horizontal"
                options={[
                  {
                    label: "Oui",
                    nativeInputProps: {
                      name: "aContacteBailleur",
                      checked: test.aContacteBailleur === true,
                      value: "true",
                      onChange: () => setAContacteBailleur(true),
                    },
                  },
                  {
                    label: "Non",
                    nativeInputProps: {
                      name: "aContacteBailleur",
                      checked: test.aContacteBailleur === false,
                      value: "false",
                      onChange: () => setAContacteBailleur(false),
                    },
                  },
                ]}
                state="default"
              />
            </div>
          )}
      </div>
      {/* Décisions */}
      {estDecide && (
        <div ref={refDecision}>
          {estDecide &&
            !test.estVise &&
            !test.estHebergeant &&
            test.aContacteAssurance &&
            (test.estProprietaire || test.aContacteBailleur) && (
              <Alert
                className="fr-my-3w"
                severity="success"
                title="Vous êtes éligible à l’indemnisation"
                description={
                  <p>Vous pouvez déposer votre demande d’indemnisation.</p>
                }
              />
            )}

          {estDecide &&
            !test.estVise &&
            !test.estHebergeant &&
            (!test.aContacteAssurance ||
              (!test.estProprietaire && !test.aContacteBailleur)) && (
              <Alert
                className="fr-my-3w"
                severity="warning"
                title={<>Votre dossier est incomplet</>}
                description={
                  <>
                    {false === test.aContacteAssurance ? (
                      <>
                        <p>
                          Avant de déposer une demande,{" "}
                          <b>
                            merci de vous rapprocher de votre assurance
                            habitation dès que possible
                          </b>
                          .
                        </p>
                        <p>
                          En cas de refus de prise en charge du sinistre, il est
                          important de demander une attestation de non prise en
                          charge.
                        </p>
                        {false === test.aContacteBailleur && (
                          <p>
                            De plus, nous vous recommandons de{" "}
                            <b>
                              prendre contact avec votre bailleur dès que
                              possible
                            </b>
                            , pour obtenir l’attestation de non prise en charge
                            des réparations
                          </p>
                        )}
                      </>
                    ) : (
                      <p>
                        Avant de déposer une demande, nous vous recommandons{" "}
                        <b>
                          prendre contact avec votre bailleur dès que possible
                        </b>
                        , pour obtenir l’attestation de non prise en charge des
                        réparations.
                      </p>
                    )}
                    <p>
                      Une fois tous les éléments rassemblés, nous pourrons
                      traiter votre demande.
                    </p>
                  </>
                }
              />
            )}

          {test.estVise && (
            <Alert
              className="fr-my-3w"
              severity="error"
              title={
                <>
                  Vous n’êtes pas éligible à l’indemnisation, car vous étiez
                  concerné par l'opération de police judiciaire exécutée à votre
                  domicile
                  <sup>1</sup>.
                </>
              }
              description={
                <p>
                  Vous ne pouvez prétendre à une indemnisation que si les forces
                  de l'ordre ont commis une
                  <Tooltip
                    title={
                      <>
                        Constitue une faute lourde toute déficience caractérisée
                        par un fait ou une série de faits traduisant
                        l'inaptitude du service public de la justice à remplir
                        la mission dont il est investi (Cour de Cassation,
                        Assemblée plénière, du 23 février 2001, 99-16.165,
                        Publié au bulletin)
                      </>
                    }
                    kind="hover"
                  >
                    <a className="fr-link" href="#">
                      faute lourde
                    </a>
                  </Tooltip>
                  au cours de leur intervention. Il vous appartiendra de prouver
                  cette faute.
                </p>
              }
            />
          )}

          {test.estHebergeant && (
            <Alert
              className="fr-my-3w"
              severity="error"
              title={
                <>
                  Vous n’êtes pas éligible à l’indemnisation, car l’intervention
                  des forces de l’ordre visait une personne de votre domicile
                  <sup>1</sup>
                </>
              }
              description={
                <>
                  (1) Article 1735 du code civil :{" "}
                  <q>
                    Le preneur est tenu des dégradations et des pertes qui
                    arrivent par le fait des personnes de sa maison ou de ses
                    sous-locataires
                  </q>
                </>
              }
            />
          )}
        </div>
      )}
      {/* Boutons */}
      <ButtonsGroup
        inlineLayoutWhen="always"
        alignment="right"
        isReverseOrder={true}
        buttons={[
          {
            disabled: !estDecide || soumissionEnCours,
            priority:
              test.estVise || test.estHebergeant ? "secondary" : "primary", // Secondary si pas eligible
            type: "submit",
            children: "Créer votre compte",
            /*
            onClick: (e) => {
              setSoumissionEnCours(true);
            },

             */
          },
          ...(estDecide
            ? [
                {
                  priority: "secondary",
                  type: "button",
                  children: "Revenir à la page d'accueil",
                } as ButtonProps,
              ]
            : []),
        ]}
      />
    </form>
  );
};
