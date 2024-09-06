import React from 'react';

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
