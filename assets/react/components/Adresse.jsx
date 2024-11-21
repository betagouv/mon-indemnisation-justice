import React, {useState,useEffect,useRef} from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";

const Adresse = ({adresse ,optionalLigne1Texte=null}) => {



  const ligne1Text = (optionalLigne1Texte!==null) ? optionalLigne1Texte : "Adresse complète";

  return (
    <div className="fr-grid-row fr-grid-row--gutters">
      <div className="fr-col-12">
        <Input
          label={ligne1Text}
          nativeInputProps={{
            name: 'ligne1',
            value: ligne1,
            onChange: ev => setLigne1(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
      <div className="fr-col-lg-2 fr-col-4">
        <Input
          label="Code postal"
          nativeInputProps={{
            name: 'codePostal',
            value: codePostal,
            onChange: ev => setCodePostal(ev.target.value),
            maxLength: 5
          }}
        />
      </div>
      <div className="fr-col-lg-10 fr-col-8">
        <Input
          label="Ville"
          nativeInputProps={{
            name: 'localite',
            value: localite,
            onChange: ev => setLocalite(ev.target.value),
            maxLength: 255
          }}
        />
      </div>
    </div>
  );
}

export default Adresse;
