import { Document } from "@/common/models";
import {
  InstitutionSecuritePublique,
  TypeInstitutionSecuritePublique,
} from "@/common/models";
import { Accordion } from "@codegouvfr/react-dsfr/Accordion";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { ToggleSwitch } from "@codegouvfr/react-dsfr/ToggleSwitch";
import React, { useCallback, useState } from "react";

type MetaDonneesAttestation = {
  estAttestation?: boolean;
  typeInstitutionSecuritePublique?: TypeInstitutionSecuritePublique;
};

export const MetaDonneesAttestationForm = function MetaDonneesAttestationForm({
  document,
  className,
}: {
  document: Document;
  className?: string;
}) {
  const [estAfiche, setAffiche] = useState(false);

  const [metaDonnees, setMetaDonnees] = useState({
    ...{
      estAttestation: false,
      typeInstitutionSecuritePublique: null,
    },
    ...document.metaDonnees,
  } as MetaDonneesAttestation);

  const sauvegarder = useCallback(
    async (metaDonnees: Partial<MetaDonneesAttestation>) => {
      const response = await fetch(
        `/agent/document/${document.id}/meta-donnees`,
        {
          method: "PATCH",
          headers: {
            "Content-type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(metaDonnees),
        },
      );
    },
    [document.id],
  );

  const setEstAttestation = useCallback(
    async (estAttestation: boolean) => {
      setMetaDonnees((metaDonnees: MetaDonneesAttestation) => {
        return {
          ...metaDonnees,
          estAttestation,
        };
      });
      await sauvegarder({ estAttestation });
    },
    [metaDonnees],
  );

  const setTypeInstitutionSecuritePublique = useCallback(
    async (institutionSecuritePublique: InstitutionSecuritePublique) => {
      setMetaDonnees((metaDonnees) => {
        return {
          ...metaDonnees,
          typeInstitutionSecuritePublique:
            institutionSecuritePublique?.type ?? null,
        };
      });
      await sauvegarder({
        typeInstitutionSecuritePublique:
          institutionSecuritePublique?.type ?? null,
      });
    },
    [metaDonnees],
  );

  return (
    <Accordion
      label="Méta-données"
      className={className}
      expanded={estAfiche}
      onExpandedChange={(expanded) => setAffiche(expanded)}
    >
      <div className="fr-grid-row fr-col-12" style={{ alignItems: "end" }}>
        <ToggleSwitch
          className="fr-col-lg-6 fr-col-12"
          label="Nouvelle attestation ?"
          labelPosition="right"
          showCheckedHint={false}
          checked={metaDonnees.estAttestation}
          onChange={(checked) => setEstAttestation(checked)}
        />

        <Select
          label="Forces de l'ordre"
          className="fr-col-lg-6 fr-col-12"
          nativeSelectProps={{
            onChange: (event) =>
              setTypeInstitutionSecuritePublique(
                InstitutionSecuritePublique.from(
                  event.target.value as TypeInstitutionSecuritePublique,
                ),
              ),
            value: metaDonnees.typeInstitutionSecuritePublique ?? "",
          }}
        >
          <option value="">Sélectionner un type</option>
          {InstitutionSecuritePublique.entries().map(([type, institution]) => (
            <option value={type} key={type}>
              {institution.libelle()}
            </option>
          ))}
        </Select>
      </div>
    </Accordion>
  );
};
