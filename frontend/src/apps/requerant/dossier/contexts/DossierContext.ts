import { createContext } from "react";

// Basé sur l'exemple https://react.dev/reference/react/useContext#scaling-up-with-context-and-a-reducer

const DossierContext = createContext(null);
const PatchDossierContext = createContext<(any) => void>(() => {});

export { DossierContext, PatchDossierContext };
