import React,{useState,useEffect} from 'react';
import { Hidden, Submit, Br } from '../../../utils/fundamental';
import { trans } from '../../../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";

const FormulaireSimple = ({raccourci=""}) => {

  const [_raccourci,setRaccourci]=useState(raccourci)
  const handleContinue = (event) => {
    //event.preventDefault();
  }

  return (
    <form onSubmit={handleContinue} name="form">
      <div className="fr-col-12">
        <Input
          label={"Code suivi"}
          nativeInputProps={{name:"raccourci", value: _raccourci, onChange: ev => setRaccourci(ev.target.value)}}
        />
      </div>
      <div className="fr-col-12">
        <Submit label={"Rechercher"} />
      </div>
    </form>
  );
}

export default FormulaireSimple;
