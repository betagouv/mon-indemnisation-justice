import React,{useState,useEffect} from 'react';
import { Uploader } from '../Uploader';
import { trans, GLOBAL_WAITING
} from '../../../translator';

export const Document = ({liasseDocumentaireIri,type,label}) => {
  const [loading,setLoading]=useState(false);

  useEffect(() => {
    const url = Routing.generate('_api_document_get_collection',{
      liasseDocumentaire: liasseDocumentaireIri,
      type: type
    });

    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        setLoading(true);
      })
      .catch(() => setLoading(true))
  },[]);

  return (
    <>
      {loading &&
      <Uploader
        label={label}
        type={type}
        liasseDocumentaireIri={liasseDocumentaireIri}
      />
      }
      {!loading &&
      <>
      {trans(GLOBAL_WAITING)}
      </>
      }
    </>
  );
}
