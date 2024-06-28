import React,{useState} from 'react';
import { trans, LOGIN_PERSONAL_ACCESS, LOGIN_PROFESSIONAL_ACCESS, HEADER_BRAND, HEADER_TITLE, HOMEPAGE_TITLE, LOGIN_TITLE } from '../../translator';
import { Header } from "@codegouvfr/react-dsfr/Header";
import { Badge } from "@codegouvfr/react-dsfr/Badge";
import parse from 'html-react-parser';

const Entete = ({user,version}) => {

  const getName = () => {
    if(user.plaintextRole == "ROLE_REDACTEUR_PRECONTENTIEUX")
      return user.username+" (rédacteur)";
    if(user.plaintextRole == "ROLE_CHEF_PRECONTENTIEUX")
      return user.username+" (chef)";
    return user.personnePhysique.prenom1+" "+user.personnePhysique.nom.toUpperCase();
  }

  const links = [];
  if(user && user.personnePhysique && user.personnePhysique.id) {
    links.push({
      iconId: 'fr-icon-user-fill',
      linkProps: { href: '#' },
      text: getName()
    });
    links.push({
      iconId: 'fr-icon-logout-box-r-fill',
      linkProps: { href: Routing.generate('app_logout') },
      text: "Déconnexion"
    });
  }
  else
    links.push({
      iconId: 'fr-icon-account-line',
      linkProps: { href: Routing.generate('app_login') },
      text: trans(LOGIN_PERSONAL_ACCESS)
    });
    links.push({
      iconId: 'fr-icon-lock-line',
      linkProps: { href: Routing.generate('app_login') },
      text: trans(LOGIN_PROFESSIONAL_ACCESS)
    });
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
      quickAccessItems={links}
    />
  );
}

export default Entete;
