import { createModal } from "@codegouvfr/react-dsfr/Modal";
import { useId, useMemo } from "react";

export type ModaleDsfr = ReturnType<typeof createModal>;

/**
 * Crée une modale DSFR avec id préfixé par `prefixe`
 *
 * Si `ouverteParDefaut` est vrai, la modale est ouverte par la lib JS du DSFR
 * dès qu'elle est présente dans le DOM.
 */
export const useModaleDsfr = (
  prefixe: string,
  { ouverteParDefaut = false }: { ouverteParDefaut?: boolean } = {},
): ModaleDsfr => {
  // `useId` peut contenir des caractères (`:`, `«`, `»`) peu sûrs dans un id HTML
  const suffixe = useId().replace(/[^a-zA-Z0-9_-]/g, "");

  return useMemo(
    () =>
      createModal({
        id: `${prefixe}-${suffixe}`,
        isOpenedByDefault: ouverteParDefaut,
      }),
    [prefixe, suffixe, ouverteParDefaut],
  );
};
