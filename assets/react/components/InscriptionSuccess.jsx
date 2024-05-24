import React from 'react';
import {trans, SECURITY_SUCCESS_CONTENT,SECURITY_SUCCESS_BTN} from '../../translator';
import { Button } from "@codegouvfr/react-dsfr/Button";
import parse from 'html-react-parser';

const InscriptionSuccess = ({user}) => {
  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-12">
        {parse(trans(SECURITY_SUCCESS_CONTENT).replace('%email%',user.email))}
        </div>
        <div className="fr-col-12">
          <Button
            linkProps={{
              href: Routing.generate('app_login')
            }}
          >
          {trans(SECURITY_SUCCESS_BTN)}
          </Button>
        </div>
      </div>
    </>
  );
}

export default InscriptionSuccess;
