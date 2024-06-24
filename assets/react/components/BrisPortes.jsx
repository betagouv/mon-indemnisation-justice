import React, {useState,useEffect} from 'react';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from './PieceJointe/PieceJointe';
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

  const CODE_STATUT_EN_COURS_DE_CONSTITUTION='EN_COURS_DE_CONSTITUTION';
  const CODE_STATUT_SIGNATURE_VALIDEE = "SIGNATURE_VALIDEE";
  const CODE_STATUT_SIGNATURE_REJETEE = "SIGNATURE_REJETEE";
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
      let btn = <></>;
      if(item.lastStatut.code === CODE_STATUT_EN_COURS_DE_CONSTITUTION)
        btn =
        <Button
          linkProps={{
            href: Routing.generate('app_bris_porte_edit',{id: item.id})
          }}
        >{trans(GLOBAL_BTN_UPDATE)}
        </Button>;
      if((item.lastStatut.code === CODE_STATUT_SIGNATURE_REJETEE)||(item.lastStatut.code===CODE_STATUT_SIGNATURE_VALIDEE))
        btn =
        <Document
          readonly={true}
          liasseDocumentaireIri={item.liasseDocumentaire}
          label={"Consulter la décision"}
          type={"signature_decision"}
        />
      tmp[tmp.length]=[
        item.reference,
        //format(dateDeclaration,"dd/MM/yy"),
        lastStatut,
        format(dateLastStatut,"dd/MM/yy HH:mm"),
        btn
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
      {/*
      <div className="fr-col-3">
        <Button
        linkProps={{
          href: Routing.generate('app_bris_porte_add')
        }}
        >{trans(BRIS_PORTE_CREATE_TITLE)}
        </Button>
      </div>
      */}
    </div>
  );
}

export default BrisPortes;
