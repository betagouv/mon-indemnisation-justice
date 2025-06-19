import React, {
  KeyboardEvent,
  KeyboardEventHandler,
  useContext,
  useDeferredValue,
  useEffect,
  useState,
} from "react";
import Civilite from "@/apps/requerant/dossier/components/Civilite";
import { Input } from "@codegouvfr/react-dsfr/Input";
import {
  DossierContext,
  PatchDossierContext,
} from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import { PaysContext } from "@/apps/requerant/dossier/contexts/PaysContext.ts";
import { randomId } from "@/apps/requerant/dossier/services/Random.ts";
import { Select } from "@codegouvfr/react-dsfr/Select";

interface GeoCommune {
  id: number;
  nom: string;
}

const PersonnePhysique = function PersonnePhysique() {
  const dossier = useContext(DossierContext);
  const pays = useContext(PaysContext);
  const patchDossier = useContext(PatchDossierContext);

  const [paysNaissance, setPaysNaissance] = useState<string>(
    dossier.requerant.personnePhysique.paysNaissance ?? null,
  );

  const [codePostalNaissance, setCodePostalNaissance] = useState(
    dossier.requerant.personnePhysique.codePostalNaissance || "",
  );

  const [communes, setCommunes] = useState<GeoCommune[]>([]);
  useEffect(() => {
    if (codePostalNaissance.length === 5) {
      const rafraichirCommunes = async (codePostal: string) => {
        const response = await fetch(`/api/communes/${codePostal}`);

        const communes = (await response.json()) as GeoCommune[];
        setCommunes(communes);
      };

      rafraichirCommunes(codePostalNaissance);
    }

    setCommunes([]);
  }, [codePostalNaissance]);

  return (
    <>
      <h3>Votre identité</h3>

      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-2 fr-col-4">
          <Civilite
            estActif={!dossier.requerant.estFranceConnect}
            civilite={dossier.requerant.personnePhysique?.civilite}
            setCivilite={(civilite) =>
              patchDossier({ requerant: { personnePhysique: { civilite } } })
            }
          />
        </div>
        <div className="fr-col-lg-7 fr-col-8">
          <Input
            disabled={dossier.requerant.estFranceConnect}
            label="Prénom(s)"
            nativeInputProps={{
              id: randomId(),
              placeholder: "Prénom(s)",
              value: dossier.requerant.personnePhysique?.prenom1 || "",
              onChange: (e) =>
                patchDossier({
                  requerant: { personnePhysique: { prenom1: e.target.value } },
                }),
              maxLength: 255,
            }}
          />
        </div>
        <div className="fr-col-lg-3 fr-col-6">
          <Input
            label="Nom d'usage"
            nativeInputProps={{
              id: randomId(),
              defaultValue: dossier.requerant.personnePhysique.nom || "",
              onChange: (e) =>
                patchDossier({
                  requerant: { personnePhysique: { nom: e.target.value } },
                }),
              maxLength: 255,
            }}
          />
        </div>
        <div className="fr-col-lg-4 fr-col-6">
          <Input
            disabled={dossier.requerant.estFranceConnect}
            label="Nom de naissance"
            nativeInputProps={{
              id: randomId(),
              value: dossier.requerant.personnePhysique.nomNaissance || "",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    personnePhysique: { nomNaissance: e.target.value },
                  },
                }),
              maxLength: 255,
            }}
          />
        </div>
        <div className="fr-col-lg-4 fr-col-6">
          <Input
            disabled={true}
            label="Adresse courriel"
            nativeInputProps={{
              id: randomId(),
              defaultValue: dossier.requerant.courriel,
              disabled: true,
              maxLength: 255,
            }}
          />
        </div>
        <div className="fr-col-lg-4 fr-col-6">
          <Input
            label="Téléphone"
            nativeInputProps={{
              id: randomId(),
              type: "tel",
              pattern: "(0,+){1}[0-9]{8,}",
              defaultValue: dossier.requerant.telephone || "",
            }}
          />
        </div>
      </div>

      <h3 className="fr-my-2w">Votre naissance</h3>

      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-3 fr-col-6">
          <Input
            disabled={dossier.requerant.estFranceConnect}
            label="Date de naissance"
            nativeInputProps={{
              id: randomId(),
              type: "date",
              value: dossier.requerant.personnePhysique.dateNaissance || "",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    personnePhysique: {
                      dateNaissance: e.target.value || null,
                    },
                  },
                }),
            }}
          />
        </div>

        <div className="fr-col-lg-3 fr-col-6">
          <Select
            disabled={dossier.requerant.estFranceConnect}
            label="Pays de naissance"
            nativeSelectProps={{
              id: randomId(),
              onChange: (e) => {
                setPaysNaissance(e.target.value);
                patchDossier({
                  requerant: {
                    personnePhysique: { paysNaissance: e.target.value },
                  },
                });
              },
              value: dossier.requerant.personnePhysique.paysNaissance || "",
            }}
          >
            <option value="" disabled hidden>
              Sélectionnez un pays
            </option>
            {pays.map((p) => (
              <option key={p.code} value={`/api/geo_pays/${p.code}`}>
                {p.nom}
              </option>
            ))}
          </Select>
        </div>

        <div className="fr-col-lg-2 fr-col-4">
          <Input
            label="Code postal"
            disabled={paysNaissance !== "/api/geo_pays/FRA"}
            nativeInputProps={{
              id: randomId(),
              type: "text",
              maxLength: 5,
              pattern: "^[0-9]{0,5}$",
              value: codePostalNaissance,
              onFocus: (e) => {
                e.target.setSelectionRange(
                  e.target.value.length + 1,
                  e.target.value.length + 1,
                );
              },
              onKeyDown: (e: KeyboardEvent<HTMLInputElement>) => {
                if (e.key !== "Backspace") {
                  const target: HTMLInputElement = e.target as HTMLInputElement;

                  const projectedValue =
                    target.value.substring(0, target.selectionStart || 0) +
                    e.key +
                    target.value.substring(target.selectionEnd || 0);

                  if (!projectedValue.match(new RegExp(target.pattern))) {
                    e.stopPropagation();
                    e.preventDefault();
                  }
                }
              },
              onChange: (e) => setCodePostalNaissance(e.target.value),
            }}
          />
        </div>

        <div className="fr-col-lg-4 fr-col-8">
          <Select
            disabled={
              dossier.requerant.estFranceConnect || communes.length === 0
            }
            label="Ville de naissance"
            nativeSelectProps={{
              id: randomId(),
              value: dossier.requerant.personnePhysique.communeNaissance ?? "",
              onChange: (e) => {
                if (
                  e.target.value !=
                  dossier.requerant.personnePhysique.communeNaissance
                ) {
                  patchDossier({
                    requerant: {
                      personnePhysique: {
                        communeNaissance: e.target.value,
                      },
                    },
                  });
                }
              },
            }}
          >
            <option value={undefined}>
              {communes.length === 0
                ? "Tapez le code postal"
                : "Sélectionnez une ville"}
            </option>
            {communes.map((commune) => (
              <option
                key={commune.id}
                value={`/api/geo_code_postals/${commune.id}`}
              >
                {commune.nom}
              </option>
            ))}
          </Select>
        </div>
      </div>

      {/*
        <div className="fr-col-lg-6 fr-col-12">
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-12">
              <Input
                label="Les 10 premiers chiffres de votre numéro de sécurité sociale"
                nativeInputProps={{
                  id: randomId(),
                  value:
                    dossier.requerant.personnePhysique.numeroSecuriteSociale ||
                    "",
                  onChange: (e) =>
                    patchDossier({
                      requerant: {
                        personnePhysique: {
                          numeroSecuriteSociale: e.target.value,
                        },
                      },
                    }),
                  maxLength: 10,
                }}
              />
            </div>
          </div>
        </div>
        */}

      <h3 className="fr-mt-3w">Votre adresse de résidence</h3>

      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Adresse"
            nativeInputProps={{
              value: dossier.requerant.adresse.ligne1 || "",
              placeholder: "Numéro de voie, rue",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    adresse: { ligne1: e.target.value },
                  },
                }),
              maxLength: 255,
            }}
          />
        </div>

        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Complément d'adresse (facultatif)"
            nativeInputProps={{
              value: dossier.requerant.adresse.ligne2 || "",
              placeholder: "Étage, escalier",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    adresse: { ligne2: e.target.value },
                  },
                }),
              maxLength: 255,
            }}
          />
        </div>
        <div className="fr-col-lg-2 fr-col-4">
          <Input
            label="Code postal"
            nativeInputProps={{
              value: dossier.requerant.adresse.codePostal || "",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    adresse: { codePostal: e.target.value },
                  },
                }),
              maxLength: 5,
            }}
          />
        </div>
        <div className="fr-col-lg-10 fr-col-8">
          <Input
            label="Ville"
            nativeInputProps={{
              value: dossier.requerant.adresse.localite || "",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    adresse: { localite: e.target.value },
                  },
                }),
              maxLength: 255,
            }}
          />
        </div>
      </div>
    </>
  );
};

export default PersonnePhysique;
