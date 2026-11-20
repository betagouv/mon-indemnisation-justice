import { createModal, ModalProps } from "@codegouvfr/react-dsfr/Modal";
import { dsfr } from "@common/services/dsfr.ts";
import { createElement, useEffect, useId, useMemo } from "react";

export type ModaleDsfr = ReturnType<typeof createModal>;

/**
 * Crée une modale DSFR d'id `id`.
 *
 * Si `ouverteParDefaut` est vrai, la modale est ouverte dès que son composant
 * est monté.
 *
 * _Note :_ l'option `isOpenedByDefault` de `createModal` n'est pas utilisée, car la
 * lib JS du DSFR n'en tient compte que pour les modales présentes au chargement
 * initial de la page (le `retrieve()` du `ModalsGroup` racine n'étant exécuté
 * qu'une seule fois), et non pour celles montées ensuite par React.
 */
export const creerModaleDsfr = ({
  id,
  ouverteParDefaut = false,
}: {
  id: string;
  ouverteParDefaut?: boolean;
}): ModaleDsfr => {
  const modale = createModal({ id, isOpenedByDefault: false });

  if (!ouverteParDefaut) {
    return modale;
  }

  const Component = (props: ModalProps) => {
    useEffect(() => {
      // Ne pas ouvrir la modale si le composant a été démonté entre-temps
      let estDemonte = false;

      dsfr(document.getElementById(id), (instance) => instance.modal).then(
        (modale) => {
          if (!estDemonte) {
            modale?.disclose();
          }
        },
      );

      return () => {
        estDemonte = true;
      };
    }, []);

    return createElement(modale.Component, props);
  };
  Component.displayName = `${id}-modal`;

  return { ...modale, Component, isOpenedByDefault: true };
};

/**
 * Crée une modale DSFR avec id préfixé par `prefixe`
 *
 * Si `ouverteParDefaut` est vrai, la modale est ouverte dès que son composant
 * est monté.
 */
export const useModaleDsfr = (
  prefixe: string,
  { ouverteParDefaut = false }: { ouverteParDefaut?: boolean } = {},
): ModaleDsfr => {
  // `useId` peut contenir des caractères (`:`, `«`, `»`) peu sûrs dans un id HTML
  const suffixe = useId().replace(/[^a-zA-Z0-9_-]/g, "");

  return useMemo(
    () =>
      creerModaleDsfr({
        id: `${prefixe}-${suffixe}`,
        ouverteParDefaut,
      }),
    [prefixe, suffixe, ouverteParDefaut],
  );
};
