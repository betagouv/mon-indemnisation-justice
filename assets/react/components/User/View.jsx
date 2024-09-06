import React from 'react';
import AdresseView from '../Adresse/View';
import PersonnePhysiqueView from '../PersonnePhysique/View';
import PersonneMoraleView from '../PersonneMorale/View';
import RepresentantLegalView from '../RepresentantLegal/View';

const UserView = function({user}) {

  return (
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <h2>{user.isPersonneMorale ? 'Personne morale' : 'Personne physique'}</h2>
        </div>
        { user.isPersonneMorale &&
          <div className="fr-col-12">
            <PersonneMoraleView personneMorale={user.personneMorale} />
            <RepresentantLegalView personnePhysique={user.personnePhysique} />
          </div>
        }
        { !user.isPersonneMorale &&
          <div className="fr-col-12">
            <PersonnePhysiqueView isPersonneMorale={user.isPersonneMorale} personnePhysique={user.personnePhysique} />
          </div>
        }
        <div className="fr-col-12">
          <AdresseView adresse={user.adresse} />
        </div>
      </div>
  );
}

export default UserView;
