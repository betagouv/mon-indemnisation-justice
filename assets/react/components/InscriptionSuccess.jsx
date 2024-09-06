import React from 'react';
import { Button } from "@codegouvfr/react-dsfr/Button";


const InscriptionSuccess = ({user}) => {
  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
            <h1>Finaliser la création de votre compte</h1>
            <p>Pour finaliser la création de votre compte, nous vous invitons à cliquer sur le lien qui vous a été
                envoyé à l'adresse email renseignée : <b>{ user.email }</b>
            </p>
            <p>Une fois votre compte créé, vous pourrez accéder à votre espace personnel pour effectuer votre demande
                d'indemnisation.
            </p>
        </div>
        <div className="fr-col-12">
          <Button
            linkProps={{
              href: Routing.generate('app_login')
            }}
          >
          J'ai finalisé la création de mon compte, je souhaite me connecter à mon espace personnel
          </Button>
        </div>
      </div>
    </>
  );
}

export default InscriptionSuccess;
