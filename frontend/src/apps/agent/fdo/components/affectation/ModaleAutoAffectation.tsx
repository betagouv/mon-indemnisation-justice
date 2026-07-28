import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import ToggleSwitch from "@codegouvfr/react-dsfr/ToggleSwitch";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";
import { FormSuggestedInput } from "@common/composants/dsfr/champs/form/FormSuggestedInput.tsx";
import { Modale } from "@common/composants/dsfr/Modale.tsx";
import { dateChiffre } from "@common/services/date.ts";
import { AgentFDO } from "@fdo/modeles/AgentFDO.ts";
import { EtablissementFDO } from "@fdo/modeles/EtablissementFDO.ts";
import { AgentManagerInterface } from "@fdo/services/agent.ts";
import { useForm } from "@tanstack/react-form";
import { useInjection } from "inversify-react";
import * as React from "react";
import { useMemo } from "react";
import { z } from "zod";

export const ModaleAutoAffectation = ({
  agent,
  onAffecte,
}: {
  agent: AgentFDO;
  onAffecte: () => Promise<void> | void;
}) => {
  const estGendarmerie = useMemo<boolean>(
    () => agent.administration.type === "GN",
    [agent.id],
  );

  const agentManager = useInjection<AgentManagerInterface>(
    AgentManagerInterface.$,
  );

  const SchemaAttribution = z.discriminatedUnion("estExempt", [
    z.object({
      estExempt: z.literal(false),
      affectation: z.object({
        etablissement: z.instanceof(EtablissementFDO, {
          error: "Veuillez sélectionner un établissement",
        }),
        dateAffectation: z
          .date({ error: "La date d'affectation est requise" })
          .max(new Date(+new Date().setHours(23, 59, 59, 9999)), {
            error: "L'affectation ne peut avoir lieu dans le futur",
          }),
      }),
    }),
    z.object({
      estExempt: z.literal(true),
      affectation: z.any().optional(),
    }),
  ]);

  const formulaire = useForm({
    validators: {
      onSubmit: SchemaAttribution,
    },
    defaultValues: {
      estExempt: false,
      affectation: {
        etablissement: undefined,
        dateAffectation: new Date(),
      },
    } as any,
    onSubmit: async ({ formApi, value }) => {
      if (formApi.state.isValid) {
        await agentManager.attribuerEtablissement({
          estExempt: value.estExempt,
          etablissement: value.affectation.etablissement,
          dateAffectation: value.affectation.dateAffectation,
        });
        await onAffecte();
      }
    },
  });

  return (
    <Modale
      iconId="fr-icon-community-line"
      title={`Affectation à ${estGendarmerie ? "une gendarmerie" : "un commissariat"}`}
      id={"modale-affectation-agent"}
      concealingBackdrop={false}
      ouverte={true}
      refermable={false}
      size="large"
    >
      <p className="fr-my-1w">
        Vous n'êtes pas encore rattaché à un établissement, veuillez renseigner
        votre affectation actuelle.
      </p>

      <form
        style={{
          display: "flex",
          flexDirection: "column",
          gap: "1.5rem",
        }}
        onSubmit={async (e) => {
          e.preventDefault();
          e.stopPropagation();
          try {
            // Rafraîchir la validation avant la soumission
            formulaire.validate("submit");
            await formulaire.handleSubmit();
          } catch (e) {
            console.error(e);
          }
        }}
      >
        <Accordion
          label={`Pourquoi dois-je renseigner ${estGendarmerie ? "ma gendarmerie" : "mon commissariat"} d'affectation ?`}
        >
          <>
            <p className="fr-my-1w">
              Nous faisons évoluer votre espace afin d'y intégrer la
              collaboration entre agents rattachés à
              {estGendarmerie ? (
                <> une même gendarmerie</>
              ) : (
                <> un même commissariat</>
              )}
              .
            </p>
            <p className="fr-my-1w">
              Des évolutions telles que la déclaration au nom d'un autre agent
              ou le partage et la collaboration de dossiers entre collègues vous
              seront proposées au cours de l'été.
            </p>
            <p className="fr-my-1w">
              Afin de rendre un tel échéancier possible, il ne nous est pas
              possible de récupérer <i>automatiquement</i> cette information
              auprès du système d'information de votre administration. C'est
              pourquoi nous avons opté de vous la demander directement.
            </p>
          </>
        </Accordion>

        <div
          className="fr-grid-row fr-grid-row--gutters fr-col-12"
          style={{ justifyItems: "baseline", alignItems: "flex-start" }}
        >
          <formulaire.Subscribe
            selector={(state) => ({
              estExempt: state.values.estExempt as boolean,
            })}
            children={({ estExempt }) => (
              <formulaire.Field
                name="affectation.etablissement"
                children={(field) => {
                  return (
                    <FormSuggestedInput<EtablissementFDO>
                      label="Établissement"
                      className="fr-m-0 fr-col-lg-9 fr-col-12"
                      champ={field}
                      estRequis={!estExempt}
                      disabled={estExempt}
                      nativeInputProps={{
                        maxLength: 255,
                        placeholder: "Angoulême, 16000",
                        defaultValue: field.state.value?.nom || "",
                        onChange: (event) => {
                          if (!event.target.value) {
                            field.setValue(undefined);
                          }
                        },
                      }}
                      estARafraichir={(valeur: string) => {
                        return valeur.replaceAll(/\s+/g, "").length >= 3;
                      }}
                      rafraichisseur={async (valeur: string) => {
                        const etablissements =
                          await agentManager.rechercherEtablissements(valeur);

                        return etablissements.map(
                          (etablissement: EtablissementFDO) => ({
                            libelle: etablissement.nom,
                            valeur: etablissement,
                          }),
                        );
                      }}
                      rafraichisseurDebounceMs={500}
                      onSelectionne={(suggestion) => field.setValue(suggestion)}
                    />
                  );
                }}
              />
            )}
          />

          <formulaire.Subscribe
            selector={(state) => ({
              aSelectionneEtablissement:
                !!state.values.affectation.etablissement?.id,
              estExempt: state.values.estExempt as boolean,
            })}
            children={({ aSelectionneEtablissement, estExempt }) => (
              <formulaire.Field
                name="affectation.dateAffectation"
                children={(field) => {
                  return (
                    <FormInput
                      label="Date d'affectation"
                      className="fr-m-0 fr-col-lg-3 fr-col-12"
                      champ={field}
                      estRequis={!estExempt}
                      disabled={!aSelectionneEtablissement || estExempt}
                      nativeInputProps={{
                        type: "date",
                        defaultValue: dateChiffre(field.state.value),
                        onChange: (e) => {
                          const date = new Date(e.target.value);

                          if (!isNaN(date.getTime())) {
                            field.setValue(date);
                          }
                        },
                      }}
                    />
                  );
                }}
              />
            )}
          />
        </div>

        {/*
        <div className="fr-grid-row fr-grid-row--gutters fr-grid-row--right">
          <Button size="small" priority="tertiary no outline">
            Je ne trouve pas mon établissement
          </Button>
        </div>
        */}

        <formulaire.Field
          name="estExempt"
          children={(field) => {
            return (
              <ToggleSwitch
                label="Je ne suis pas rattaché à un établissement"
                inputTitle="Je ne suis pas rattaché à un établissement"
                showCheckedHint={false}
                helperText={
                  "Si vous n'êtes pas rattaché à un commissariat / une gendarmerie"
                }
                defaultChecked={field.state.value}
                onChange={(checked: boolean) => field.setValue(checked)}
              />
            );
          }}
        />

        <ButtonsGroup
          inlineLayoutWhen="always"
          buttonsSize="medium"
          alignment="right"
          buttons={[
            {
              children: "Enregistrer",
              priority: "primary",
              nativeButtonProps: {
                type: "submit",
                role: "submit",
              },
            },
          ]}
        />
      </form>
    </Modale>
  );
};
