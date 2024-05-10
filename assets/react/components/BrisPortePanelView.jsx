import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { trans, BRIS_PORTE_SECTION,USER_SECTION,
  GLOBAL_STEP_NEXT,GLOBAL_STEP_PREVIOUS,
  BRIS_PORTE_EDIT_UPDATE_CONSTITUE
} from '../../translator';
import { Stepper } from "@codegouvfr/react-dsfr/Stepper";
import BrisPorteView from './BrisPorte/View';
import UserView from './User/View';

const BrisPortePanelView = function({brisPorte}) {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">

          <div className="fr-col-12">
            <UserView user={brisPorte.requerant} />
          </div>

          <div className="fr-col-12">
            <BrisPorteView brisPorte={brisPorte} />
          </div>

      </div>
    </div>
  );
}

export default BrisPortePanelView;
