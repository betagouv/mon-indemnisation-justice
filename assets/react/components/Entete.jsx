import React,{useState} from 'react';
import { trans, HEADER_BRAND, HEADER_TITLE, HOMEPAGE_TITLE, LOGIN_TITLE } from '../../translator';
import { Header } from "@codegouvfr/react-dsfr/Header";
import { Badge } from "@codegouvfr/react-dsfr/Badge";
import parse from 'html-react-parser';

const Entete = ({user,version}) => {
  return (
    <Header
      brandTop={parse(trans(HEADER_BRAND))}
      homeLinkProps={{
        href: '/',
        title: trans(HEADER_TITLE)
      }}
      serviceTitle={
        <>
        {trans(HOMEPAGE_TITLE)} {version.branch && <Badge as="span" noIcon severity="success">{version.branch}</Badge>}
        </>
      }
      quickAccessItems={[
        {
          iconId: 'fr-icon-git-repository-private-line',
          linkProps: { href: Routing.generate('app_login') },
          text: trans(LOGIN_TITLE)
        }
      ]}
    />
  );
}

export default Entete;
