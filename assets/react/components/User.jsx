import React, {useState,useEffect} from 'react';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import Adresse from './Adresse';
import PersonnePhysique from './PersonnePhysique';
import PersonneMorale from './PersonneMorale';
import RepresentantLegal from './RepresentantLegal'

const User = function({ user, id, toggleIsPersonneMorale}) {
  const [isPersonneMorale, setIsPersonneMorale] = useState(user.isPersonneMorale);
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    if(!loading) {
      setLoading(true);
      return;
    }
    const url =Routing.generate('_api_requerant_patch',{id:id});
    const data = { isPersonneMorale: isPersonneMorale };

    fetch(url, {
      method: 'PATCH',
      redirect: 'error',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
    .then((response) => response.json())
    .then((data) => console.log('backup user'))
    ;
  },[isPersonneMorale])

  const _isPersonneMorale=isPersonneMorale;
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
                      checked: isPersonneMorale,
                      onChange: ()=> {setIsPersonneMorale(true);toggleIsPersonneMorale()}

                    }
                  },
                  {
                    label: "Non",
                    nativeInputProps: {
                      checked: !isPersonneMorale,
                      onChange: ()=> {setIsPersonneMorale(false);toggleIsPersonneMorale()}

                    }
                  }
                ]}
              />
            </div>
          </div>
        </div>
        <a name="identite"></a>
        { _isPersonneMorale &&
          <>
            <div id="pr-bris-de-porte_personne-morale">
              <div className="fr-grid-row fr-grid-row--gutters">
                <div className="fr-col-12">
                  <section className="pr-form-section fr-p-4w">
                    <PersonneMorale personneMorale={user.personneMorale} />
                    <Adresse adresse={user.adresse} />
                  </section>
                </div>
                <div className="fr-col-12">
                  <section className="pr-form-section fr-p-4w">
                    <RepresentantLegal personnePhysique={user.personnePhysique} />
                  </section>
                </div>
            </div>
            </div>
          </>
        }
        { !_isPersonneMorale &&
          <>
          <div id="pr-bris-de-porte_personne-physique">
            <div className="fr-grid-row fr-grid-row--gutters">
              <div className="fr-col-12">
                <section className="pr-form-section fr-p-4w">
                  <PersonnePhysique isPersonneMorale={isPersonneMorale} personnePhysique={user.personnePhysique} />
                  <Adresse adresse={user.adresse} />
                </section>
              </div>
            </div>
          </div>
          </>
        }
      </>
  );
}

User.propTypes = {
}

export default User;
