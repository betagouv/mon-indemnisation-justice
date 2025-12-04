import { createContext } from "react";

// Basé sur l'exemple https://react.dev/reference/react/useContext#scaling-up-with-context-and-a-reducer

const DossierContext = createContext<any>({});
const PatchDossierContext = createContext<(dossier: any) => void>(() => {});

export { DossierContext, PatchDossierContext };
