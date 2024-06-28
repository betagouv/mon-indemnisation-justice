import React,{useState} from 'react';
import { trans, LOGIN_PERSONAL_ACCESS, LOGIN_PROFESSIONAL_ACCESS,
    HEADER_BRAND, HEADER_TITLE, HOMEPAGE_TITLE, LOGIN_TITLE,
    HOMEPAGE_ALTERNATIVE_TITLE, PREJUDICE_TITLE, REQUERANT_HOMEPAGE_TITLE
} from '../../translator';
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

  const link_add_prejudice = {linkProps: {href: Routing.generate('app_category'),target: '_self'},text: trans(PREJUDICE_TITLE)};
  const link_qui_sommes_nous = {linkProps: {href: Routing.generate('app_qui_sommes_nous'),target: '_self'},text: trans(HOMEPAGE_ALTERNATIVE_TITLE)};
  const link_consult_prejudice = {linkProps: {href: Routing.generate('app_redirect'),target: '_self'},text: trans(REQUERANT_HOMEPAGE_TITLE)};

  const navbar = [];
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
    if(!user.plaintextRole)
      navbar.push(link_add_prejudice);
    navbar.push(link_consult_prejudice);
  }
  else {
    navbar.push(link_add_prejudice);
    navbar.push(link_qui_sommes_nous);

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
  }

  return (
    <Header
      brandTop={parse(trans(HEADER_BRAND))}
      homeLinkProps={{
        href: Routing.generate('app_category'),
        title: trans(HEADER_TITLE)
      }}
      serviceTitle={
        <>
        {trans(HOMEPAGE_TITLE)} {version.branch && <Badge as="span" noIcon severity="success">{version.branch}</Badge>}
        </>
      }
      quickAccessItems={links}
      navigation={navbar}
    />
  );
}

export default Entete;
