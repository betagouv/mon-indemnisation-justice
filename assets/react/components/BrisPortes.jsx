import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { format } from "date-fns";
import {
  trans,
  PREJUDICE_FIELD_REFERENCE,
  BRIS_PORTE_CREATE_TITLE,
  REQUERANT_HOMEPAGE_TITLE,
  SINISTRE_FIELD_DATE_DECLARATION,
  SINISTRE_FIELD_STATUT,
  SINISTRE_FIELD_DATE_DERNIER_STATUT,
  GLOBAL_ACTIONS,
  GLOBAL_BTN_UPDATE
} from '../../translator';
const BrisPortes = function({items}) {

  const [isLoading, setIsLoading]=useState(false);

  const [data,setData] = useState([]);

  const headers = [
    trans(PREJUDICE_FIELD_REFERENCE),
    //trans(SINISTRE_FIELD_DATE_DECLARATION),
    trans(SINISTRE_FIELD_STATUT),
    trans(SINISTRE_FIELD_DATE_DERNIER_STATUT),
    trans(GLOBAL_ACTIONS)
  ];

  useEffect(() => {
    if(true===isLoading)
      return;
    let tmp=[];
    items.map((item) => {
      const dateDeclaration = Date.parse(item.dateDeclaration);
      const lastStatut = item.lastStatut.libelle;
      const dateLastStatut = Date.parse(item.lastStatut.date);
      tmp[tmp.length]=[
        item.reference,
        //format(dateDeclaration,"dd/MM/yy"),
        lastStatut,
        format(dateLastStatut,"dd/MM/yy HH:mm"),
        <Button
        linkProps={{
          href: Routing.generate('app_bris_porte_edit',{id: item.id})
        }}
        >{trans(GLOBAL_BTN_UPDATE)}
        </Button>
      ];
    });
    setData(tmp);
    setIsLoading(true);
  },[isLoading])

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
          href: Routing.generate('app_bris_porte_add')
        }}
        >{trans(BRIS_PORTE_CREATE_TITLE)}
        </Button>
      </div>
    </div>
  );
}

export default BrisPortes;
