import React,{useState,useEffect,useRef} from 'react';
import { Loading } from '../../utils/fundamental';
import { Document } from '../PieceJointe/PieceJointe';

const Recapitulatif = ({
  isPersonneMorale,
  personneMoraleLiasseDocumentaireUri,
  personnePhysiqueLiasseDocumentaireUri,
  prejudiceUri
}) => {
  const TYPE_BRIS_PORTE="BrisPorte";

  const [loading,setLoading]=useState(false);
  const [prejudice,setPrejudice]=useState({});
  const [type,setType]=useState("");

  var locked = useRef(false);

  useEffect(() => {

    if(locked.current === true)
      return;
    locked.current=true;

    Promise
      .all([prejudiceUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([_pr]) => {
          setPrejudice(_pr);
          setType(_pr["@type"]);
          setLoading(true);
        })
        .catch(() => {})
      ;
  },[]);

  return (
    <>
      <h3>Documents à joindre obligatoirement à votre demande</h3>
      {loading &&
      <>
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label="Attestation d'informations complétée par les forces de l'ordre"
          type={"attestation_information"}
        />
        }
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label="Photos de la porte endommagée"
          hint_text="Seuls les frais de remise en état à l'identique de la porte endommagée seront indemnisés"
          type={"photo_prejudice"}
        />
        }
        {!isPersonneMorale &&
        <Document
          readonly={true}
          liasseDocumentaireIri={personnePhysiqueLiasseDocumentaireUri}
          label="Copie de ma pièce d'identité recto-verso"
          hint_text=" "
          type={"carte_identite"}
        />
        }
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label="Facture acquittée et copie du relevé de compte bancaire attestant du paiement"
          hint_text=" "
          type={"preuve_paiement_facture"}
        />
        }
        {isPersonneMorale &&
        <Document
          readonly={true}
          liasseDocumentaireIri={personneMoraleLiasseDocumentaireUri}
          label="Relevé d'identité bancaire de ma société"
          hint_text=" "
          type={"rib"}
        />
        }
        {!isPersonneMorale &&
        <Document
          readonly={true}
          liasseDocumentaireIri={personnePhysiqueLiasseDocumentaireUri}
          label="Mon relevé d'identité bancaire"
          hint_text=" "
          type={"rib"}
        />
        }
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label="Titre de propriété"
          hint_text=" "
          type={"titre_propriete"}
        />
        }
      </>
      }
      {!loading &&
      <Loading />
      }
    </>
  );
}

export default Recapitulatif;
