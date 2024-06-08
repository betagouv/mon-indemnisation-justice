import React,{useState,useEffect} from 'react';
import {default as RecapitulatifPersonneMorale} from '../PersonneMorale/Recapitulatif';
import {default as RecapitulatifRepresentantLegal} from '../RepresentantLegal/Recapitulatif';
import { Br,Loading } from '../../utils/fundamental';
import { Button } from "@codegouvfr/react-dsfr/Button";
import {trans,GLOBAL_REVERT} from '../../../translator';

const Recapitulatif = ({user,brisPorte,revert}) => {

  const personneMoraleUri = Routing.generate('_api_personne_morale_get',{id: user.personneMorale.id});
  const personnePhysiqueUri = Routing.generate('_api_personne_physique_get',{id: user.personnePhysique.id});
  const brisPorteUri = Routing.generate('_api_bris_porte_get',{id: brisPorte.id});
  const userUri = Routing.generate('_api_user_get',{id:user.pId});
  const [loading,setLoading]=useState(false);
  const [lUser,setLUser]=useState({});
  useEffect(() => {
    if(true === loading)
      return;
    fetch(userUri)
      .then((response) => response.json())
      .then((data) => {
        setLUser(data);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-6">
      {loading && lUser && lUser.isPersonneMorale &&
        <div className="fr-grid-row">
          <div className="fr-col-12">
            <RecapitulatifPersonneMorale adresseUri={lUser.adresse} uri={lUser.personneMorale} />
            <Br space={1}/>
            <RecapitulatifRepresentantLegal uri={lUser.personnePhysique} />
            <Br space={2}/>
            <Button
              onClick={revert}
              priority="secondary"
            >{trans(GLOBAL_REVERT)}</Button>
          </div>
          <div className="fr-col-12">
          </div>
        </div>

      }
      {!loading &&
        <Loading />
      }
      </div>
    </div>
  );
}

export default Recapitulatif;
