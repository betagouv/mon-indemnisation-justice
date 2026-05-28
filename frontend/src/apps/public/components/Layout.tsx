import React from "react";
import "@/style/index.css";
import { headerFooterDisplayItem } from "@codegouvfr/react-dsfr/Display";
import Footer from "@codegouvfr/react-dsfr/Footer";
import { Header } from "@codegouvfr/react-dsfr/Header";

type Props = {
  children: React.ReactNode;
};

export const Layout = ({ children }: Props) => (
  <>
    <Header
      brandTop={
        <>
          Ministère
          <br /> Justice
        </>
      }
      homeLinkProps={{ href: "/", title: "Accueil - Mon Indemnisation Justice" }}
      serviceTitle="Mon Indemnisation Justice"
      navigation={[
        {
          text: "Accueil",
          linkProps: { href: "/" },
        },
      ]}
    />

    <main role="main" className="fr-p-2w">
      <div className="fr-container fr-container--fluid">{children}</div>
    </main>

    <Footer
      accessibility="non compliant"
      contentDescription="Mon Indemnisation Justice est un service public numérique du Ministère de la Justice permettant de déclarer un déni de justice et de suivre votre dossier d'indemnisation."
      bottomItems={[headerFooterDisplayItem]}
      termsLinkProps={{ href: "/mentions-legales" }}
    />
  </>
);
