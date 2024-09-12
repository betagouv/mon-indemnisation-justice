import React, {useState,useEffect} from 'react';
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { format } from "date-fns";
const BrisPortes = function({items}) {

  const [isLoading, setIsLoading]=useState(false);

  const [data,setData] = useState([]);

  const headers = [
    "Référence",
    "Statut du dossier",
    "Date du dernier statut",
    "Code suivi",
    // Actions désactivées en attendant la refonte des statuts
    "Actions",
  ];

  useEffect(() => {
    if(true===isLoading)
      return;
    let tmp=[];
    items.map((item) => {
      const dateCreation = Date.parse(item.dateCreation);
      const dateDeclaration = Date.parse(item.dateDeclaration);
      const lastStatut = `Demande d'indemnisation ${dateDeclaration ? "constituée" : "en cours de constitution"}`;
      let btn = <></>;
      if(!dateDeclaration)
        btn =
        <Button
          linkProps={{
            href: Routing.generate('app_bris_porte_edit',{id: item.id})
          }}
          size="small"
          iconId="fr-icon-ball-pen-line"
        >Terminer
        </Button>;
      /*if((item.lastStatut.code === CODE_STATUT_SIGNATURE_REJETEE)||(item.lastStatut.code===CODE_STATUT_SIGNATURE_VALIDEE))
        btn =
        <Document
          readonly={true}
          liasseDocumentaireIri={item.liasseDocumentaire}
          label={"Consulter la décision"}
          type={"signature_decision"}
        />*/
      tmp[tmp.length]=[
        item.reference,
        //format(dateDeclaration,"dd/MM/yy"),
        lastStatut,
        format(dateDeclaration || dateCreation,"dd/MM/yy HH:mm"),
        item.raccourci,
        // Actions désactivées en attendant la refonte des statuts
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
