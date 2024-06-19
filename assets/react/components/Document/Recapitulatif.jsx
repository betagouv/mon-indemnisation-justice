import React,{useState,useEffect,useRef} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import { Document } from '../PieceJointe/PieceJointe';
import {trans,
  DOCUMENT_ATTESTATION_INFORMATION_TITLE,
  DOCUMENT_PHOTO_BRIS_PORTE_TITLE,
  DOCUMENT_PHOTO_BRIS_PORTE_HINT,
  DOCUMENT_PIECE_IDENTITE_TITLE,
  DOCUMENT_PIECE_IDENTITE_HINT,
  DOCUMENT_FACTURE_TITLE,
  BRIS_PORTE_PJ_SECTION,
  DOCUMENT_FACTURE_HINT,
  DOCUMENT_RIB_PRO_TITLE,
  DOCUMENT_RIB_PRO_HINT,
  DOCUMENT_RIB_TITLE,
  DOCUMENT_RIB_HINT,
  DOCUMENT_TITRE_PROPRIETE_TITLE,
  DOCUMENT_TITRE_PROPRIETE_HINT
} from '../../../translator';
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
      <h3>{trans(BRIS_PORTE_PJ_SECTION)}</h3>
      {loading &&
      <>
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label={trans(DOCUMENT_ATTESTATION_INFORMATION_TITLE)}
          type={"attestation_information"}
        />
        }
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label={trans(DOCUMENT_PHOTO_BRIS_PORTE_TITLE)}
          hint_text={trans(DOCUMENT_PHOTO_BRIS_PORTE_HINT)}
          type={"photo_prejudice"}
        />
        }
        {!isPersonneMorale &&
        <Document
          readonly={true}
          liasseDocumentaireIri={personnePhysiqueLiasseDocumentaireUri}
          label={trans(DOCUMENT_PIECE_IDENTITE_TITLE)}
          hint_text={trans(DOCUMENT_PIECE_IDENTITE_HINT)}
          type={"carte_identite"}
        />
        }
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label={trans(DOCUMENT_FACTURE_TITLE)}
          hint_text={trans(DOCUMENT_FACTURE_HINT)}
          type={"preuve_paiement_facture"}
        />
        }
        {isPersonneMorale &&
        <Document
          readonly={true}
          liasseDocumentaireIri={personneMoraleLiasseDocumentaireUri}
          label={trans(DOCUMENT_RIB_PRO_TITLE)}
          hint_text={trans(DOCUMENT_RIB_PRO_HINT)}
          type={"rib"}
        />
        }
        {!isPersonneMorale &&
        <Document
          readonly={true}
          liasseDocumentaireIri={personnePhysiqueLiasseDocumentaireUri}
          label={trans(DOCUMENT_RIB_TITLE)}
          hint_text={trans(DOCUMENT_RIB_HINT)}
          type={"rib"}
        />
        }
        {(type===TYPE_BRIS_PORTE) &&
        <Document
          readonly={true}
          liasseDocumentaireIri={prejudice.liasseDocumentaire}
          label={trans(DOCUMENT_TITRE_PROPRIETE_TITLE)}
          hint_text={trans(DOCUMENT_TITRE_PROPRIETE_HINT)}
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
