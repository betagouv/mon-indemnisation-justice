import React from 'react';
import ReadOnlyInput from '../ReadOnlyInput';
import SecuriteSocialeView from '../SecuriteSociale/View';
import { normalizeDate } from '../../utils/cast';

const RepresentantLegalView = function({personnePhysique}) {

  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h5>Identité du représentant légal</h5>
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="Civilité"
            value={personnePhysique.civilite.libelle}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="Nom d'usage"
            value={personnePhysique.nom}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
        <ReadOnlyInput
          label="Nom de naissance"
          value={personnePhysique.nomNaissance}
        />
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <ReadOnlyInput
            label="Prénom(s)"
            value={personnePhysique.prenom1}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="&nbsp;"
            value={personnePhysique.prenom2}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="&nbsp;"
            value={personnePhysique.prenom3}
          />
        </div>
        <div className="fr-col-1">
        </div>
      </div>
      <div className="fr-grid-row">
        <div className="fr-col-3">
          <ReadOnlyInput
            label="Date de naissance"
            value={normalizeDate(personnePhysique.dateNaissance)}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-3">
          <ReadOnlyInput
            label="Ville de naissance"
            value={personnePhysique.communeNaissance}
          />
        </div>
        <div className="fr-col-1">
        </div>
        <div className="fr-col-6">
          <SecuriteSocialeView
            codeSS={personnePhysique.codeSecuriteSociale}
            numeroSS={personnePhysique.numeroSecuriteSociale}
          />
        </div>
      </div>
    </>
  );
}

export default RepresentantLegalView;
