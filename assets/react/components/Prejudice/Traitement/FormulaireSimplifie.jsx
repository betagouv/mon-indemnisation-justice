import React,{useState,useEffect} from 'react';
import { Br,Wysiwyg } from '../../../utils/fundamental';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { Button } from "@codegouvfr/react-dsfr/Button";

const FormulaireSimplifie = ({prejudice}) => {

  const [indemniteProposee,setIndemniteProposee]=useState("0.00");
  const [note,setNote]=useState(prejudice.note);
  useEffect(() => {
    const form = {note: note,indemniteProposee: indemniteProposee};
    console.log(form);
  },[note,indemniteProposee]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <Wysiwyg label='Note' value={note} setValue={setNote}/>
      </div>
      <div className="fr-col-12">
        <Input
          label={"Indemnité proposée"}
          iconId="fr-icon-money-euro-box-line"
          nativeInputProps={{
            value: indemniteProposee,
            onChange: ev => setIndemniteProposee(ev.target.value),
            maxLength: 11
          }}
        />
      </div>
      <div className="fr-col-12">
        <Br/>
      </div>
      <div className="fr-col-12">
        <ul className="fr-btns-group fr-btns-group--inline-sm">
          <li>
            <Button onClick={() => {}}>Rejet</Button>
          </li>
          <li>
            <Button onClick={() => {}}>Proposer l'indemnisation</Button>
          </li>
        </ul>
      </div>
    </div>
  );
}

export default FormulaireSimplifie;
