import React, { useContext} from 'react';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import Adresse from './Adresse';
import PersonnePhysique from './PersonnePhysique';
import PersonneMorale from './PersonneMorale';
import {DossierContext, PatchDossierContext} from "../contexts/DossierContext.ts";
import Civilite from "./Civilite.jsx";
import {Input} from "@codegouvfr/react-dsfr/Input.js";

const User = function() {
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
                      onChange: () => patchDossier({requerant: {isPersonneMorale: true}})
                    }
                  },
                  {
                    label: "Non",
                    nativeInputProps: {
                      checked: !dossier.requerant.isPersonneMorale,
                      onChange: () => patchDossier({ requerant: {isPersonneMorale: false}})
                    }
                  }
                ]}
              />
            </div>
          </div>
        </div>
        <a name="identite"></a>
        { dossier.requerant.isPersonneMorale &&
          <>
            <div id="pr-bris-de-porte_personne-morale">
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12">
                  <section className="pr-form-section fr-p-4w">
                    <PersonneMorale />
                    <Adresse adresse={dossier.requerant.adresse} />
                  </section>
                </div>
                <div className="fr-col-12">
                    <section className="pr-form-section fr-p-4w">
                        <h3>Identité du représentant légal</h3>
                        <div className="fr-grid-row fr-grid-row--gutters">
                            <div className="fr-col-lg-2 fr-col-4">
                                <Civilite
                                    civilite={dossier.requerant.personnePhysique.civilite}
                                    setCivilite={(civilite) => patchDossier({ requerant: { personnePhysique: { civilite }}})}
                                />
                            </div>
                            <div className="fr-col-lg-10 fr-col-8">
                                {/*
                                <Input
                                    label="Prénom(s)"
                                    stateRelatedMessage="Le champs est obligatoire"
                                    nativeInputProps={{
                                        placeholder: "Premier prénom",
                                        value: dossier.requerant.personnePhysique.prenom1,
                                        onChange: (e) => patchDossier({requerant: {personnePhysique: { prenom1: e.target.value}}})
                                    }}
                                />
                                */}
                            </div>
                            <div className="fr-col-lg-6 fr-col-12">
                                <Input
                                    label="Nom de naissance"

                                    //stateRelatedMessage="Le champs est obligatoire"
                                    nativeInputProps={{
                                        value: dossier.requerant.personnePhysique.nomNaissance,
                                        onChange: (e) => patchDossier({requerant: {personnePhysique: { nomNaissance: e.target.value}}})
                                    }}
                                />
                            </div>
                            <div className="fr-col-lg-6 fr-col-12">
                                <Input
                                    label="Nom d'usage"
                                    nativeInputProps={{
                                        value: dossier.requerant.personnePhysique.nom,
                                        onChange: (e) => patchDossier({requerant: {personnePhysique: { nom: e.target.value}}})
                                    }}
                                />
                            </div>
                            <div className="fr-col-lg-6 fr-col-12">
                                <Input
                                    label="Courriel professionnel"
                                    nativeInputProps={{
                                        value: dossier.requerant.personnePhysique.email,
                                        onChange: (e) => patchDossier({requerant: {personnePhysique: { email: e.target.value}}})
                                    }}
                                />
                            </div>
                            <div className="fr-col-lg-6 fr-col-12">
                                <Input
                                    label="Numéro de téléphone professionnel"
                                    nativeInputProps={{
                                        value: dossier.requerant.personnePhysique.telephone,
                                        onChange: (e) => patchDossier({requerant: {personnePhysique: { telephone: e.target.value}}})
                                    }}
                                />
                            </div>
                        </div>
                    </section>
                </div>
              </div>
            </div>
          </>
        }
          {!dossier.requerant.isPersonneMorale &&
              <>
                  <div id="pr-bris-de-porte_personne-physique">
                      <div className="fr-grid-row fr-grid-row--gutters">
                          <div className="fr-col-12">
                              <section className="pr-form-section fr-p-4w">
                                  <PersonnePhysique/>
                                  <Adresse adresse={dossier.requerant.adresse}/>
                              </section>
                          </div>
                      </div>
                  </div>
              </>
          }
      </>
  );
}

User.propTypes = {}

export default User;
