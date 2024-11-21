import React, { useEffect, useContext} from 'react';
import Civilite from './Civilite';
import { Input } from "@codegouvfr/react-dsfr/Input";
import {DossierContext,PatchDossierContext} from "../contexts/DossierContext.ts";

const PersonnePhysique = function() {
  const dossier = useContext(DossierContext);
  const patchDossier = useContext(PatchDossierContext);

  return (
    <>
      <h3>Votre identité</h3>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-3 fr-col-4">
          <Civilite
              civilite={dossier.requerant.personnePhysique?.civilite}
              setCivilite={(civilite) => patchDossier({ requerant: { personnePhysique: { civilite } } })}
          />
        </div>
        <div className="fr-col-lg-9 fr-col-8">
          <Input
            label="Prénom(s)"
            nativeInputProps={{
              placeholder: "Prénom(s)",
              value: dossier.requerant.personnePhysique?.prenom1 || "",
              onChange: (e) => patchDossier({
                   requerant: { personnePhysique: { prenom1: e.target.value } }
              }),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Nom de naissance"
            nativeInputProps={{
              value: dossier.requerant.personnePhysique.nomNaissance || "",
              onChange: (e) => patchDossier({ requerant: { personnePhysique: { nomNaissance: e.target.value}}}),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Nom d'usage"
            nativeInputProps={{
              value: dossier.requerant.personnePhysique.nom || "",
              onChange: (e) => patchDossier({ requerant: { personnePhysique: { nom: e.target.value}}}),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-3 fr-col-12">
          <Input
            label="Date de naissance"
            nativeInputProps={{
              type: 'date',
              value: dossier.requerant.personnePhysique.dateNaissance || "",
              onChange: (e) => patchDossier({ requerant: { personnePhysique: { dateNaissance: e.target.value}}}),
            }}
          />
        </div>
        <div className="fr-col-lg-9 fr-col-12">
          <Input
            label="Ville de naissance"
            nativeInputProps={{
              value: dossier.requerant.personnePhysique.communeNaissance || "",
              onChange: (e) => patchDossier({ requerant: { personnePhysique: { communeNaissance: e.target.value}}}),
              maxLength: 255
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
            <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12">
                    <Input
                        label="Les 10 premiers chiffres de votre numéro de sécurité sociale"
                        nativeInputProps={{
                            value: dossier.requerant.personnePhysique.numeroSecuriteSociale || "",
                            onChange: (e) => patchDossier({ requerant: { personnePhysique: { numeroSecuriteSociale: e.target.value}}}),
                            maxLength: 10
                          }}
                        />
                    </div>
                </div>
            </div>
        </div>
    </>
  );
}

PersonnePhysique.propTypes = {}

export default PersonnePhysique;
