import "@/style/index.css";
import { useLocation } from "@tanstack/react-router";
import React, { useEffect, useRef } from "react";

type Props = {
  children: React.ReactNode;
};

/**
 * Le header et le footer du site sont déjà rendus par le gabarit Twig qui enveloppe cette SPA
 * (base.html.twig -> _header.html.twig / _footer.html.twig, hérité par public.html.twig) : ce
 * composant ne doit donc fournir que le contenu de la page, sans dupliquer header/footer/landmark
 * `main` (le Twig parent a déjà son propre `<main role="main">`).
 */
export const Layout = ({ children }: Props) => {
  const scrollRef = useRef<HTMLDivElement>(null);
  const location = useLocation();

  // Ramène le scroll en haut du contenu à chaque changement de page
  useEffect(() => {
    scrollRef.current?.scrollIntoView({ behavior: "instant" });
  }, [location.pathname]);

  return (
    <div ref={scrollRef} className="fr-p-2w">
      <div className="fr-container fr-container--fluid">{children}</div>
    </div>
  );
};
