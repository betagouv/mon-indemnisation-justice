import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';
import { Document } from '../PieceJointe/PieceJointe';
import {trans,
  DOCUMENT_ATTESTATION_INFORMATION_TITLE,
  DOCUMENT_PHOTO_BRIS_PORTE_TITLE,
  DOCUMENT_PHOTO_BRIS_PORTE_HINT,
  DOCUMENT_PIECE_IDENTITE_TITLE,
  DOCUMENT_PIECE_IDENTITE_HINT,
  DOCUMENT_FACTURE_TITLE,
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
  personneMoraleUri,
  personnePhysiqueUri,
  prejudiceUri
}) => {

  const TYPE_BRIS_PORTE="BrisPorte";

  const [loading,setLoading]=useState(false);
  const [personneMorale,setPersonneMorale]=useState({});
  const [personnePhysique,setPersonnePhysique]=useState({});
  const [prejudice,setPrejudice]=useState({});
  const [type,setType]=useState("");
  useEffect(() => {
    if(true===loading)
      return;
    Promise
      .all([personneMoraleUri,personnePhysiqueUri,prejudiceUri]
        .map((u) => fetch(u).then((response) => response.json()))
      )
      .then(([_pm,_pp,_pr]) => {
          setPrejudice(_pr);
          setPersonneMorale(_pm);
          setPersonnePhysique(_pp);
          setType(_pr["@type"]);
          console.log(_pr);
          console.log(_pm);
          console.log(_pp);
          setLoading(true);
        })
        .catch(() => {})
      ;
  },[]);

  return (
    <>
    {loading &&
      <>
        {(type===TYPE_BRIS_PORTE) &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={prejudice.liasseDocumentaire}
            label={trans(DOCUMENT_ATTESTATION_INFORMATION_TITLE)}
            type={"attestation_information"}
          />
          <Br space={2} />
        </div>
        }
        {(type===TYPE_BRIS_PORTE) &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={prejudice.liasseDocumentaire}
            label={trans(DOCUMENT_PHOTO_BRIS_PORTE_TITLE)}
            hint_text={trans(DOCUMENT_PHOTO_BRIS_PORTE_HINT)}
            type={"photo_prejudice"}
          />
          <Br space={2} />
        </div>
        }
        {!isPersonneMorale &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={personnePhysique.liasseDocumentaire}
            label={trans(DOCUMENT_PIECE_IDENTITE_TITLE)}
            hint_text={trans(DOCUMENT_PIECE_IDENTITE_HINT)}
            type={"carte_identite"}
          />
          <Br space={2} />
        </div>
        }
        {(type===TYPE_BRIS_PORTE) &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={prejudice.liasseDocumentaire}
            label={trans(DOCUMENT_FACTURE_TITLE)}
            hint_text={trans(DOCUMENT_FACTURE_HINT)}
            type={"preuve_paiement_facture"}
          />
          <Br space={2} />
        </div>
        }
        {isPersonneMorale &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={personneMorale.liasseDocumentaire}
            label={trans(DOCUMENT_RIB_PRO_TITLE)}
            hint_text={trans(DOCUMENT_RIB_PRO_HINT)}
            type={"rib"}
          />
          <Br space={2} />
        </div>
        }
        {!isPersonneMorale &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={personnePhysique.liasseDocumentaire}
            label={trans(DOCUMENT_RIB_TITLE)}
            hint_text={trans(DOCUMENT_RIB_HINT)}
            type={"rib"}
          />
          <Br space={2} />
        </div>
        }
        {(type===TYPE_BRIS_PORTE) &&
        <div className="fr-col-12">
          <Document
            readonly={true}
            liasseDocumentaireIri={prejudice.liasseDocumentaire}
            label={trans(DOCUMENT_TITRE_PROPRIETE_TITLE)}
            hint_text={trans(DOCUMENT_TITRE_PROPRIETE_HINT)}
            type={"titre_propriete"}
          />
          <Br space={2} />
        </div>
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
