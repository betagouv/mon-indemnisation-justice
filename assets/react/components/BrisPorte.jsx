import React, { useContext } from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import Requerant from './Requerant';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {DossierContext, PatchDossierContext} from "../contexts/DossierContext.ts";
import Civilite from "./Civilite.jsx";

const BrisPorte = () => {
  const dossier = useContext(DossierContext);
  const patchDossier = useContext(PatchDossierContext);

  return (
    <div className="fr-grid-row fr-grid-row--gutters fr-mb-4w">
      <div className="fr-col-12">
        <a name="bris-de-porte"></a>
        <section className="pr-form-section fr-p-4w">
          <h3>Informations sur le bris de porte</h3>
          <div className="fr-grid-row fr-grid-row--gutters">
            <div className="fr-col-lg-4 fr-col-12">
              <Input
                label="Date de l'opération de police judiciaire"
                nativeInputProps={{
                    type: 'date',
                    value: dossier.dateOperationPJ || "",
                    onChange: (e) => patchDossier({ dateOperationPJ: e.target.value })
                }}
              />
            </div>
            <div className="fr-col-offset-8"></div>
              <div className="fr-col-12">
                  <div className="fr-grid-row fr-grid-row--gutters">
                      <div className="fr-col-12">
                          <Input
                              label="Adresse complète du logement concerné par le bris de porte"
                              nativeInputProps={{
                                  value: dossier.adresse.ligne1 || "",
                                  onChange: (e) => patchDossier({adresse: { ligne1: e.target.value}}),
                                  maxLength: 255
                              }}
                          />
                      </div>
                      <div className="fr-col-lg-2 fr-col-4">
                          <Input
                              label="Code postal"
                              nativeInputProps={{
                                  value: dossier.adresse.codePostal || "",
                                  onChange: (e) => patchDossier({adresse: { codePostal: e.target.value}}),
                                  maxLength: 5
                              }}
                          />
                      </div>
                      <div className="fr-col-lg-10 fr-col-8">
                          <Input
                              label="Ville"
                              nativeInputProps={{
                                  value: dossier.adresse.localite || "",
                                  onChange: (e) => patchDossier({adresse: { localite: e.target.value}}),
                                  maxLength: 255
                              }}
                          />
                      </div>
                  </div>

              </div>
              <div className="fr-col-12">
                  <RadioButtons
                      legend="S'agit-il d'une porte blindée ?"
                      orientation='horizontal'
                      options={[
                          {label: "Oui",
                              nativeInputProps: {
                                  checked: (dossier.isPorteBlindee === true),
                                  onChange: () => patchDossier({isPorteBlindee: true})
                              }
                          },
                          {label: "Non",
                              nativeInputProps: {
                                  checked: (dossier.isPorteBlindee !== true),
                                  onChange: () => patchDossier({isPorteBlindee: false})
                              }
                          },
                      ]}
                  />
              </div>
              <div className="fr-col-12">
                  <Input
                      label="Si vous la connaissez, précisez l'identité de la personne recherchée"
                      nativeInputProps={{
                          value: dossier.identitePersonneRecherchee || "",
                          onChange: (e) => patchDossier({ identitePersonneRecherchee: e.target.value}),
                          maxLength: 255
                      }}
              />
            </div>
            <div className="fr-col-12">
              <Requerant
                qualiteRequerant={dossier.qualiteRequerant}
                setQualiteRequerant={(qualiteRequerant) => patchDossier({ qualiteRequerant })}
                precisionRequerant={dossier.precisionRequerant}
                setPrecisionRequerant={(precisionRequerant) => patchDossier({ precisionRequerant })}
              />
            </div>
          </div>
        </section>
      </div>
      <div className="fr-col-12">
          <section className="pr-form-section fr-p-4w">
              <h3>Attestation remise par les force de l'ordre à</h3>
              <div className="fr-grid-row fr-grid-row--gutters">
                  <div className="fr-col-lg-3 fr-col-4">
                      <Civilite
                          civilite={dossier.receveurAttestation.civilite}
                          setCivilite={(civilite) => patchDossier({receveurAttestation: { civilite }})}
                      />
                  </div>
                  <div className="fr-col-lg-9 fr-col-8">
                      <Input
                          label="Prénom(s)"
                          nativeInputProps={{
                              placeholder: "Prénom(s)",
                              value: dossier.receveurAttestation.prenom1 || "",
                              onChange: (e) => patchDossier({ receveurAttestation: { prenom1: e.target.value}}),
                              maxLength: 255
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Nom de naissance"
                          nativeInputProps={{
                              value: dossier.receveurAttestation.nomNaissance || "",
                              onChange: (e) => patchDossier({ receveurAttestation: { nomNaissance: e.target.value}}),
                              maxLength: 255
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label={"Nom d'usage"}
                          nativeInputProps={{
                              value: dossier.receveurAttestation.nom || "",
                              onChange: (e) => patchDossier({ receveurAttestation: { nom: e.target.value}}),
                              maxLength: 255
                          }}
                      />
                  </div>
                  <div className="fr-col-12">
                      <Requerant
                          qualiteText={"Qualité"}
                          precisionText={"Préciser sa qualité"}
                          qualiteRequerant={dossier.receveurAttestation.qualiteRequerant}
                          setQualiteRequerant={(qualiteRequerant) => patchDossier({ receveurAttestation:{ qualiteRequerant } })}
                          precisionRequerant={dossier.receveurAttestation.precision}
                          setPrecisionRequerant={(precisionRequerant) => patchDossier({ receveurAttestation:{ precision: precisionRequerant } })}
                      />
                  </div>
              </div>
          </section>
      </div>
      <a name="service-enqueteur"></a>
      <div className="fr-col-12">
          <section className="pr-form-section fr-p-4w">
              <h3>Service enquêteur</h3>
              <div className="fr-grid-row fr-grid-row--gutters">
                  <div className="fr-col-12">
                      <Input
                          label="Nom du service"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.nom || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: { nom: e.target.value}}),
                        }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Téléphone"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.telephone || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: { telephone: e.target.value}}),
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Courriel"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.courriel || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: { courriel: e.target.value}}),
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Numéro de procès-verbal"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.numeroPV || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: {numeroPV: e.target.value}}),
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Juridiction"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.juridiction || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: { juridiction: e.target.value}}),
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Nom du magistrat"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.magistrat || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: { magistrat: e.target.value}}),
                          }}
                      />
                  </div>
                  <div className="fr-col-lg-6 fr-col-12">
                      <Input
                          label="Numéro de parquet ou d'instruction"
                          nativeInputProps={{
                              value: dossier.serviceEnqueteur.numeroParquet || "",
                              onChange: (e) => patchDossier({ serviceEnqueteur: { numeroParquet: e.target.value}}),
                          }}
                      />
                  </div>
              </div>
          </section>
      </div>
    </div>
  );
}

export default BrisPorte;
