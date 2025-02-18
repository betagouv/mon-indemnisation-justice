import React, { useContext } from "react";
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import PersonnePhysique from "@/apps/requerant/dossier/components/PersonnePhysique.jsx";
import PersonneMorale from "@/apps/requerant/dossier/components/PersonneMorale.jsx";
import {
  DossierContext,
  PatchDossierContext,
} from "@/apps/requerant/dossier/contexts/DossierContext.ts";
import Civilite from "@/apps/requerant/dossier/components/Civilite.jsx";
import { Input } from "@codegouvfr/react-dsfr/Input.js";

const User = function () {
  const dossier = useContext(DossierContext);
  const patchDossier = useContext(PatchDossierContext);

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <div className="pr-case_bris-de-porte_is-personne-morale fr-px-2w fr-pt-4w">
            <RadioButtons
              orientation="horizontal"
              legend="Votre demande d'indemnisation concerne une personne morale (société, entreprise, association, fondation etc.)"
              options={[
                {
                  label: "Oui",
                  nativeInputProps: {
                    checked: dossier.requerant.isPersonneMorale,
                    onChange: () =>
                      patchDossier({ requerant: { isPersonneMorale: true } }),
                  },
                },
                {
                  label: "Non",
                  nativeInputProps: {
                    checked: !dossier.requerant.isPersonneMorale,
                    onChange: () =>
                      patchDossier({ requerant: { isPersonneMorale: false } }),
                  },
                },
              ]}
            />
          </div>
        </div>
      </div>
      {dossier.requerant.isPersonneMorale && (
        <>
          <div id="pr-bris-de-porte_personne-morale">
            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-12">
                <section className="pr-form-section fr-p-4w">
                  <PersonneMorale />

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
                        label="Complément d'adresse"
                        hintText={"Facultatif"}
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
                </section>
              </div>
              <div className="fr-col-12">
                <section className="pr-form-section fr-p-4w">
                  <h3>Identité du représentant légal</h3>
                  <div className="fr-grid-row fr-grid-row--gutters">
                    <div className="fr-col-lg-2 fr-col-4">
                      <Civilite
                        civilite={dossier.requerant.personnePhysique.civilite}
                        setCivilite={(civilite) =>
                          patchDossier({
                            requerant: { personnePhysique: { civilite } },
                          })
                        }
                      />
                    </div>
                    <div className="fr-col-lg-10 fr-col-8">
                      <Input
                        label="Prénom(s)"
                        nativeInputProps={{
                          placeholder: "Premier prénom",
                          value:
                            dossier.requerant.personnePhysique.prenom1 || "",
                          onChange: (e) =>
                            patchDossier({
                              requerant: {
                                personnePhysique: { prenom1: e.target.value },
                              },
                            }),
                        }}
                      />
                    </div>
                    <div className="fr-col-lg-6 fr-col-12">
                      <Input
                        label="Nom de naissance"
                        nativeInputProps={{
                          value:
                            dossier.requerant.personnePhysique.nomNaissance ||
                            "",
                          onChange: (e) =>
                            patchDossier({
                              requerant: {
                                personnePhysique: {
                                  nomNaissance: e.target.value,
                                },
                              },
                            }),
                        }}
                      />
                    </div>
                    <div className="fr-col-lg-6 fr-col-12">
                      <Input
                        label="Nom d'usage"
                        nativeInputProps={{
                          value: dossier.requerant.personnePhysique.nom || "",
                          onChange: (e) =>
                            patchDossier({
                              requerant: {
                                personnePhysique: { nom: e.target.value },
                              },
                            }),
                        }}
                      />
                    </div>
                    <div className="fr-col-lg-6 fr-col-12">
                      <Input
                        label="Courriel professionnel"
                        nativeInputProps={{
                          value: dossier.requerant.personnePhysique.email || "",
                          onChange: (e) =>
                            patchDossier({
                              requerant: {
                                personnePhysique: { email: e.target.value },
                              },
                            }),
                        }}
                      />
                    </div>
                    <div className="fr-col-lg-6 fr-col-12">
                      <Input
                        label="Numéro de téléphone professionnel"
                        nativeInputProps={{
                          value:
                            dossier.requerant.personnePhysique.telephone || "",
                          onChange: (e) =>
                            patchDossier({
                              requerant: {
                                personnePhysique: { telephone: e.target.value },
                              },
                            }),
                        }}
                      />
                    </div>
                  </div>
                </section>
              </div>
            </div>
          </div>
        </>
      )}
      {!dossier.requerant.isPersonneMorale && (
        <>
          <div id="pr-bris-de-porte_personne-physique">
            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-12">
                <section className="pr-form-section fr-p-4w">
                  <PersonnePhysique />
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
                </section>
              </div>
            </div>
          </div>
        </>
      )}
    </>
  );
};

User.propTypes = {};

export default User;
