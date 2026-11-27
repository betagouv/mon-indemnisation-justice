import { Dossier, getCiviliteLibelle } from "@/apps/requerant/models";
import {
  type EtablissementBancaire,
  type ReferenceBancaire,
  type TypeReferenceBancaire,
} from "@/apps/requerant/models/ReferenceBancaire.ts";
import { EtablissementBancaireManagerInterface } from "@/apps/requerant/services/EtablissementBancaireManager.ts";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { ChampsSegmentes } from "@common/composants/dsfr/champs/ChampsSegmentes.tsx";
import { FormInput } from "@common/composants/dsfr/champs/form/FormInput.tsx";
import { FormRadioButtons } from "@common/composants/dsfr/champs/form/FormRadioButtons.tsx";
import {
  estCleRibValide,
  estIbanValide,
  extraireCodeBanqueIban,
  LONGUEURS_RIB,
  normaliserIban,
  retirerAccents,
} from "@common/services/banque.ts";
import { useForm, useStore } from "@tanstack/react-form";
import { useQuery } from "@tanstack/react-query";
import { useInjection } from "inversify-react";
import React, { useEffect, useMemo, useRef } from "react";
import { z } from "zod";

type ChampsRib = Pick<
  ReferenceBancaire,
  "codeBanque" | "codeGuichet" | "numeroCompte" | "cleRib"
>;

const CHAMPS_RIB = [
  "codeBanque",
  "codeGuichet",
  "numeroCompte",
  "cleRib",
] as const satisfies (keyof ChampsRib)[];

const SEGMENTS_RIB = [
  {
    label: "Code banque",
    longueur: LONGUEURS_RIB.codeBanque,
    inputMode: "numeric",
  },
  {
    label: "Code guichet",
    longueur: LONGUEURS_RIB.codeGuichet,
    inputMode: "numeric",
  },
  { label: "Numéro de compte", longueur: LONGUEURS_RIB.numeroCompte },
  { label: "Clé RIB", longueur: LONGUEURS_RIB.cleRib, inputMode: "numeric" },
] as const;

const estRibComplet = (rib: ChampsRib): boolean =>
  CHAMPS_RIB.every((champ) => rib[champ].length === LONGUEURS_RIB[champ]);

/**
 * Seule la complétude des champs bloque la saisie : la validité des clés (RIB, IBAN) donne lieu à un simple
 * avertissement, les coordonnées étant ensuite vérifiées par un agent.
 */
const schemaReferenceBancaire = z
  .object({
    type: z.enum(["RIB", "IBAN", ""]),
    rib: z.object({
      codeBanque: z.string(),
      codeGuichet: z.string(),
      numeroCompte: z.string(),
      cleRib: z.string(),
    }),
    iban: z.string(),
    nomBanque: z
      .string()
      .trim()
      .min(1, { error: "Le nom de l'établissement bancaire est requis" }),
    titulaireDuCompte: z
      .string()
      .trim()
      .min(1, { error: "Le titulaire du compte est requis" }),
  })
  .superRefine(({ type, rib, iban }, contexte) => {
    if (type === "") {
      contexte.addIssue({
        code: "custom",
        path: ["type"],
        message: "Indiquez si vous saisissez un RIB ou un IBAN",
      });
    } else if (type === "RIB" && !estRibComplet(rib)) {
      contexte.addIssue({
        code: "custom",
        path: ["rib"],
        message:
          "Le RIB doit être complet : code banque (5 chiffres), code guichet (5 chiffres), numéro de compte (11 caractères) et clé (2 chiffres)",
      });
    } else if (type === "IBAN" && normaliserIban(iban).length === 0) {
      contexte.addIssue({
        code: "custom",
        path: ["iban"],
        message: "L'IBAN est requis",
      });
    }
  });

/**
 * Titulaire proposé par défaut : civilité, prénom et nom du requérant, ou raison sociale d'une personne morale, sans
 * accent (comme sur la plupart des relevés bancaires).
 */
export const titulaireParDefaut = (dossier: Dossier): string => {
  if (dossier.estPersonneMorale) {
    return retirerAccents(dossier.personneMorale?.raisonSociale ?? "").trim();
  }

  const personne = dossier.personnePhysique?.personne;

  return retirerAccents(
    [
      personne?.civilite ? getCiviliteLibelle(personne.civilite) : "",
      personne?.prenom ?? "",
      personne?.nom ?? "",
    ]
      .filter((partie) => partie.trim().length > 0)
      .join(" "),
  );
};

const LogoEtablissement = ({
  etablissement,
}: {
  etablissement?: EtablissementBancaire;
}) =>
  etablissement?.logo ? (
    <img
      src={etablissement.logo}
      // Décoratif : le nom de l'établissement figure dans le champ voisin
      alt=""
      style={{ height: "2.5rem", maxWidth: "6rem", objectFit: "contain" }}
    />
  ) : (
    <span
      className="fr-icon-bank-line fr-icon--lg"
      aria-hidden="true"
      style={{ color: "var(--text-mention-grey)" }}
    />
  );

export const EtapeReferenceBancaire = ({
  dossier,
  referenceBancaire,
  annuler,
  etapeSuivante,
}: {
  dossier: Dossier;
  referenceBancaire?: ReferenceBancaire;
  annuler: () => void;
  etapeSuivante: (referenceBancaire: ReferenceBancaire) => void;
}) => {
  const etablissementBancaireManager =
    useInjection<EtablissementBancaireManagerInterface>(
      EtablissementBancaireManagerInterface.$,
    );

  const { data: etablissements } = useQuery({
    queryKey: ["etablissements-bancaires"],
    queryFn: () => etablissementBancaireManager.lister(),
    // Référentiel quasi statique : chargé une fois par session
    staleTime: Infinity,
  });

  const etablissementsParCode = useMemo(
    () =>
      new Map(
        (etablissements ?? []).map((etablissement) => [
          etablissement.codeBanque,
          etablissement,
        ]),
      ),
    [etablissements],
  );

  const formulaire = useForm({
    defaultValues: {
      type: (referenceBancaire?.type ?? "") as TypeReferenceBancaire | "",
      rib: {
        codeBanque: referenceBancaire?.codeBanque ?? "",
        codeGuichet: referenceBancaire?.codeGuichet ?? "",
        numeroCompte: referenceBancaire?.numeroCompte ?? "",
        cleRib: referenceBancaire?.cleRib ?? "",
      },
      iban: referenceBancaire?.iban ?? "",
      nomBanque: referenceBancaire?.nomBanque ?? "",
      titulaireDuCompte:
        referenceBancaire?.titulaireDuCompte ?? titulaireParDefaut(dossier),
    },
    validators: {
      onSubmit: schemaReferenceBancaire,
    },
    onSubmit: ({ value }) => {
      const estRib = value.type === "RIB";

      etapeSuivante({
        type: value.type as TypeReferenceBancaire,
        codeBanque: estRib ? value.rib.codeBanque : "",
        codeGuichet: estRib ? value.rib.codeGuichet : "",
        numeroCompte: estRib ? value.rib.numeroCompte : "",
        cleRib: estRib ? value.rib.cleRib : "",
        iban: estRib ? "" : normaliserIban(value.iban),
        nomBanque: value.nomBanque.trim(),
        titulaireDuCompte: value.titulaireDuCompte.trim(),
      });
    },
  });

  const type = useStore(formulaire.store, (etat) => etat.values.type);
  const rib = useStore(formulaire.store, (etat) => etat.values.rib);
  const iban = useStore(formulaire.store, (etat) => etat.values.iban);
  const ibanQuitte = useStore(
    formulaire.store,
    (etat) => etat.fieldMeta.iban?.isBlurred ?? false,
  );

  const codeBanque =
    type === "RIB"
      ? /^\d{5}$/.test(rib.codeBanque)
        ? rib.codeBanque
        : undefined
      : type === "IBAN"
        ? extraireCodeBanqueIban(iban)
        : undefined;
  const etablissement = codeBanque
    ? etablissementsParCode.get(codeBanque)
    : undefined;

  // Le nom de l'établissement reconnu est pré-rempli, sans écraser un nom saisi par l'usager
  const nomPreRempli = useRef<string | null>(null);
  useEffect(() => {
    if (!etablissement) {
      return;
    }

    const nomActuel = formulaire.getFieldValue("nomBanque").trim();
    if (nomActuel === "" || nomActuel === nomPreRempli.current) {
      formulaire.setFieldValue("nomBanque", etablissement.nom);
      nomPreRempli.current = etablissement.nom;
    }
  }, [etablissement, formulaire]);

  const avertissement =
    type === "RIB" && estRibComplet(rib) && !estCleRibValide(rib)
      ? "La clé RIB ne correspond pas au code banque, au code guichet et au numéro de compte saisis."
      : type === "IBAN" &&
          ibanQuitte &&
          iban.trim() !== "" &&
          !estIbanValide(iban)
        ? "Cet IBAN ne semble pas valide (format ou clé de contrôle)."
        : null;

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        e.stopPropagation();
        void formulaire.handleSubmit();
      }}
    >
      <p>
        Indiquez les coordonnées du compte bancaire sur lequel l'indemnité vous
        sera versée.
      </p>

      <formulaire.Field
        name="type"
        children={(champ) => (
          <FormRadioButtons
            legend="Vous disposez :"
            orientation="horizontal"
            estRequis={true}
            champ={champ}
            options={[
              {
                label: "d'un RIB",
                hintText: "Code banque, code guichet, numéro de compte et clé",
                nativeInputProps: {
                  value: "RIB",
                  checked: champ.state.value === "RIB",
                  onChange: () => champ.handleChange("RIB"),
                },
              },
              {
                label: "d'un IBAN",
                hintText: "Commençant par 2 lettres, ex : FR76…",
                nativeInputProps: {
                  value: "IBAN",
                  checked: champ.state.value === "IBAN",
                  onChange: () => champ.handleChange("IBAN"),
                },
              },
            ]}
          />
        )}
      />

      {type === "RIB" && (
        <formulaire.Field
          name="rib"
          children={(champ) => (
            <ChampsSegmentes
              legende="Relevé d'identité bancaire"
              description="Vous pouvez coller votre RIB complet dans le premier champ"
              estRequis={true}
              segments={[...SEGMENTS_RIB]}
              valeurs={CHAMPS_RIB.map((nom) => champ.state.value[nom])}
              onChange={(valeurs) =>
                champ.handleChange(
                  Object.fromEntries(
                    CHAMPS_RIB.map((nom, index) => [nom, valeurs[index]]),
                  ) as ChampsRib,
                )
              }
              onBlur={champ.handleBlur}
              filtrer={(saisie) =>
                saisie.replace(/[^0-9A-Za-z]/g, "").toUpperCase()
              }
              erreur={
                champ.state.meta.isValid
                  ? undefined
                  : champ.state.meta.errors.at(0)?.message
              }
            />
          )}
        />
      )}

      {type === "IBAN" && (
        <formulaire.Field
          name="iban"
          children={(champ) => (
            <FormInput
              label="IBAN"
              hintText="Exemple : FR76 3000 6000 0112 3456 7890 189"
              estRequis={true}
              champ={champ}
              nativeInputProps={{
                maxLength: 42,
                autoComplete: "off",
                spellCheck: false,
                value: champ.state.value,
                onChange: (e) =>
                  champ.handleChange(e.target.value.toUpperCase()),
                onBlur: champ.handleBlur,
              }}
            />
          )}
        />
      )}

      {avertissement && (
        <Alert
          className="fr-mb-3w"
          severity="warning"
          small
          description={`${avertissement} Vérifiez votre saisie : vous pouvez néanmoins continuer, vos coordonnées seront contrôlées par nos équipes.`}
        />
      )}

      {type !== "" && (
        <>
          <div className="fr-grid-row fr-grid-row--gutters fr-grid-row--bottom">
            <div className="fr-col-12 fr-col-md-6">
              <div
                style={{ display: "flex", gap: "1rem", alignItems: "flex-end" }}
              >
                <div className="fr-mb-3w" style={{ flexShrink: 0 }}>
                  <LogoEtablissement etablissement={etablissement} />
                </div>
                <div style={{ flexGrow: 1 }}>
                  <formulaire.Field
                    name="nomBanque"
                    children={(champ) => (
                      <FormInput
                        label="Établissement bancaire"
                        hintText={
                          etablissement
                            ? "Reconnu d'après le code banque"
                            : "Nom de votre banque"
                        }
                        estRequis={true}
                        champ={champ}
                        nativeInputProps={{
                          maxLength: 255,
                          value: champ.state.value,
                          onChange: (e) => champ.handleChange(e.target.value),
                          onBlur: champ.handleBlur,
                        }}
                      />
                    )}
                  />
                </div>
              </div>
            </div>
            <div className="fr-col-12 fr-col-md-6">
              <formulaire.Field
                name="titulaireDuCompte"
                children={(champ) => (
                  <FormInput
                    label="Titulaire du compte"
                    hintText="Tel qu'il figure sur votre relevé bancaire"
                    estRequis={true}
                    champ={champ}
                    nativeInputProps={{
                      maxLength: 255,
                      value: champ.state.value,
                      onChange: (e) => champ.handleChange(e.target.value),
                      onBlur: champ.handleBlur,
                    }}
                  />
                )}
              />
            </div>
          </div>
        </>
      )}

      <ButtonsGroup
        className="fr-mt-3w"
        buttonsSize="small"
        inlineLayoutWhen="always"
        alignment="right"
        buttons={[
          {
            children: "Annuler",
            priority: "tertiary no outline",
            onClick: annuler,
          },
          {
            children: "Avancer vers l'étape suivante",
            priority: "secondary",
            nativeButtonProps: {
              role: "submit",
              type: "submit",
            },
          },
        ]}
      />
    </form>
  );
};
