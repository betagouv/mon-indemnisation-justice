import React,{useState} from 'react';
import { trans,HOMEPAGE_TITLE } from '../../translator';
import { Header } from "@codegouvfr/react-dsfr/Header";
import { Badge } from "@codegouvfr/react-dsfr/Badge";

const Entete = ({user,version}) => {

  return (
    <Header
      brandTop={<>Ministère<br/>justice</>}
      homeLinkProps={{
        href: '/',
        title: 'Ministère justice'
      }}
      serviceTitle={
        <>
        {trans(HOMEPAGE_TITLE)} <Badge as="span" noIcon severity="success">{version.label}</Badge>
        </>
      }
    />
  );
}

export default Entete;
