import React, {useState,useEffect} from 'react';
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Document } from './PieceJointe/PieceJointe';
import { format } from "date-fns";
const BrisPortes = function({items}) {

  const CODE_STATUT_EN_COURS_DE_CONSTITUTION='EN_COURS_DE_CONSTITUTION';
  const CODE_STATUT_SIGNATURE_VALIDEE = "SIGNATURE_VALIDEE";
  const CODE_STATUT_SIGNATURE_REJETEE = "SIGNATURE_REJETEE";
  const [isLoading, setIsLoading]=useState(false);

  const [data,setData] = useState([]);

  const headers = [
    "Référence",
    "Statut du dossier",
    "Date du dernier statut",
    "Code suivi",
    // Actions désactivées en attendant la refonte des statuts
    //"Actions",
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
        >Modifier
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
        item.raccourci,
        // Actions désactivées en attendant la refonte des statuts
        //btn
      ];
    });
    setData(tmp);
    setIsLoading(true);
  },[isLoading])

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Table
          caption="Mes dossiers"
          data={data}
          headers={headers}
          fixed
        />
      </div>
      <div className="fr-col-9">
      </div>
      {}
    </div>
  );
}

export default BrisPortes;
