import React, {useState,useEffect} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import Adresse from './Adresse';
import { Br } from '../utils/fundamental';
import PersonnePhysique from './PersonnePhysique';
import PersonneMorale from './PersonneMorale';
import RepresentantLegal from './RepresentantLegal';
import { fr } from "@codegouvfr/react-dsfr";
import { ToggleSwitch } from "@codegouvfr/react-dsfr/ToggleSwitch";
import { trans, USER_FIELD_IS_PERSONNE_MORALE,GLOBAL_YES,GLOBAL_NO } from '../../translator';

const User = function({user,id,toggleIsPersonneMorale}) {

  const [isPersonneMorale, setIsPersonneMorale] = useState(user.isPersonneMorale);
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    if(!loading) {
      setLoading(true);
      return;
    }
    const url =Routing.generate('_api_user_patch',{id:id});
    const data = { isPersonneMorale: isPersonneMorale };

    fetch(url, {
      method: 'PATCH',
      headers: {'Content-Type': 'application/merge-patch+json'},
      body: JSON.stringify(data)
    })
    .then((response) => response.json())
    .then((data) => console.log('backup user'))
    ;
  },[isPersonneMorale])

  const _isPersonneMorale=isPersonneMorale;
  return (
      <div className="fr-grid-row">
        <div className="fr-col-12">
          <RadioButtons
            orientation="horizontal"
            legend={trans(USER_FIELD_IS_PERSONNE_MORALE)}
            options={[
              {
                label: trans(GLOBAL_YES),
                nativeInputProps: {
                  checked: isPersonneMorale,
                  onChange: ()=> {setIsPersonneMorale(true);toggleIsPersonneMorale()}

                }
              },
              {
                label: trans(GLOBAL_NO),
                nativeInputProps: {
                  checked: !isPersonneMorale,
                  onChange: ()=> {setIsPersonneMorale(false);toggleIsPersonneMorale()}

                }
              }
            ]}
          />
        </div>
        { _isPersonneMorale &&
          <>
            <div className="fr-col-12">
              <PersonneMorale personneMorale={user.personneMorale} />
            </div>
            <div className="fr-col-12">
              <Adresse adresse={user.adresse} />
            </div>
            <div className="fr-col-12">
              <Br space={2}/>
            </div>
            <div className="fr-col-12">
              <RepresentantLegal personnePhysique={user.personnePhysique} />
            </div>
          </>
        }
        { !_isPersonneMorale &&
          <>
            <div className="fr-col-12">
              <PersonnePhysique isPersonneMorale={isPersonneMorale} personnePhysique={user.personnePhysique} />
            </div>
            <div className="fr-col-12">
              <Adresse adresse={user.adresse} />
            </div>
          </>
        }
      </div>
  );
}

User.propTypes = {
}

export default User;
