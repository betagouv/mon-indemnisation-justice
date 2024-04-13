import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import {
  trans,
  BRIS_PORTE_CREATE_TITLE,
  REQUERANT_HOMEPAGE_TITLE,
  SINISTRE_FIELD_DATE_DECLARATION,
  SINISTRE_FIELD_STATUT,
  SINISTRE_FIELD_DATE_DERNIER_STATUT,
  GLOBAL_ACTIONS
} from '../../translator';
const BrisPortes = function(props) {

  const data = [
    /**['Donnée','Donnée'],*/
  ];

  const headers = [
    trans(SINISTRE_FIELD_DATE_DECLARATION),
    trans(SINISTRE_FIELD_STATUT),
    trans(SINISTRE_FIELD_DATE_DERNIER_STATUT),
    trans(GLOBAL_ACTIONS)
  ];

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Table
          caption={trans(REQUERANT_HOMEPAGE_TITLE)}
          data={data}
          headers={headers}
          fixed
        />
      </div>
      <div className="fr-col-9">
      </div>
      <div className="fr-col-3">
        <Button
        linkProps={{
          href: Routing.generate('app_declare_bris_porte')
        }}
        >{trans(BRIS_PORTE_CREATE_TITLE)}
        </Button>
      </div>
    </div>
  );
}

export default BrisPortes;
