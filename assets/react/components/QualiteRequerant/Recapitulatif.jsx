import React,{useState,useEffect} from 'react';
import { Br,Loading } from '../../utils/fundamental';

const Recapitulatif = ({uri,precision=""}) => {

  const [loading,setLoading]=useState(false);
  const [qualiteRequerant, setQualiteRequerant]=useState({});

  useEffect(() => {
    if(true===loading)
      return;
    fetch(uri)
      .then((response) => response.json())
      .then((data) => {
        setQualiteRequerant(data);
        setLoading(true);
      })
      .catch(() => {})
    ;
  },[])

  const pr = precision?" - "+precision:"";
  return (
    <>
    {loading &&
      <>
      J'effectue ma demande en qualité de : <b>{qualiteRequerant.libelle} {pr}</b>
      </>
    }
    {!loading &&
      <Loading />
    }
    </>
  );
}

export default Recapitulatif;
