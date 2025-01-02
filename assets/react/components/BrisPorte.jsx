import React, { useContext } from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import Requerant from './Requerant';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import {DossierContext, PatchDossierContext} from "@/react/contexts/DossierContext.ts";
import Civilite from "@/react/components/Civilite.jsx";

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
                    onChange: (e) => patchDossier({ dateOperationPJ: e.target.value || null})
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
    </div>
  );
}

export default BrisPorte;
