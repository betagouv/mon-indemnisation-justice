import React, {useState,useEffect} from 'react';

import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { format } from "date-fns";

const Prejudices = function({items}) {

  const [isLoading, setIsLoading]=useState(false);

  const [data,setData] = useState([]);

  const headers = [
    "Référence",
    "Statut du dossier",
    "Date du dernier statut",
    "Actions"
  ];

  useEffect(() => {
    if(true===isLoading)
      return;
    let tmp=[];
    items.map((item) => {
      const lastStatut = item.lastStatut.libelle;
      const dateLastStatut = Date.parse(item.lastStatut.date);
      let href = null;
      if(item.discriminator === 'BrisPorte')
        href = Routing.generate('agent_bris_porte_consulter',{id: item.id});
      tmp[tmp.length]=[
        item.reference,
        lastStatut,
        format(dateLastStatut,"dd/MM/yy HH:mm"),
        <Button
        linkProps={{ href: href }}
        >Consulter
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
          caption="Dossiers à valider"
          data={data}
          headers={headers}
          fixed
        />
      </div>
    </div>
  );
}

export default Prejudices;
