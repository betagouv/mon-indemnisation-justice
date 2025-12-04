import { createContext } from "react";

type Pays = {
  nom: string;
  code: string;
};
const PaysContext = createContext<Pays[]>([]);

export { PaysContext, type Pays };
