import React,{useState} from 'react';
import { trans, LOGIN_PERSONAL_ACCESS,
    HEADER_BRAND, HEADER_TITLE, HOMEPAGE_TITLE, HOMEPAGE_SUIVI_DOSSIER,
    HOMEPAGE_ALTERNATIVE_TITLE, PREJUDICE_TITLE, REQUERANT_HOMEPAGE_TITLE
} from '../../translator';
import { Header } from "@codegouvfr/react-dsfr/Header";
import { Badge } from "@codegouvfr/react-dsfr/Badge";
import parse from 'html-react-parser';

const Entete = ({user,version,withNavbar=true}) => {

  const getName = () => {
    if(user.plaintextRole == "ROLE_REDACTEUR_PRECONTENTIEUX")
      return user.username+" (rédacteur)";
    if(user.plaintextRole == "ROLE_CHEF_PRECONTENTIEUX")
      return user.username+" (chef)";
    return `${user.personnePhysique.prenom1 || ''} ${user.personnePhysique.nom?.toUpperCase() || ''}`;
  }

  const link_add_prejudice = {linkProps: {href: Routing.generate('app_category'),target: '_self'},text: trans(PREJUDICE_TITLE)};
  const link_qui_sommes_nous = {linkProps: {href: Routing.generate('app_qui_sommes_nous'),target: '_self'},text: trans(HOMEPAGE_ALTERNATIVE_TITLE)};
  const link_suivi_dossier = {linkProps: {href: Routing.generate('app_suivi_mon_dossier'),target: '_self'},text: trans(HOMEPAGE_SUIVI_DOSSIER)};
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
    if(!user.plaintextRole) {
      navbar.push(link_add_prejudice);
      navbar.push(link_suivi_dossier);
    }
    navbar.push(link_consult_prejudice);
  }
  else {
    navbar.push(link_add_prejudice);
    navbar.push(link_suivi_dossier);
    navbar.push(link_qui_sommes_nous);

    links.push({
      iconId: 'fr-icon-government-line',
      linkProps: { href: Routing.generate('app_agent_securite_connexion') },
      text: "Agent"
    });
    links.push({
      iconId: 'fr-icon-account-line',
      linkProps: { href: Routing.generate('app_login') },
      text: trans(LOGIN_PERSONAL_ACCESS)
    });

  }

  const _navbar = (true===withNavbar) ? navbar : [];

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
      navigation={_navbar}
    />
  );
}

export default Entete;
