export enum ActionContentieuse {
  Oui = "oui",
  Non = "non",
}

export enum TypeDecision {
  JugementPremiereInstance = "jugement_premiere_instance",
  ArretCourAppel = "arret_cour_appel",
  ArretCourCassation = "arret_cour_cassation",
  Aucune = "aucune",
}

export enum PieceProcedure {
  Assignation = "assignation",
  DecisionsJuge = "decisions_juge",
  Calendrier = "calendrier",
  Ecritures = "ecritures",
  Convocations = "convocations",
  Renvoi = "renvoi",
  Echanges = "echanges",
  Appel = "appel",
}

export enum PreuvesDiligences {
  Oui = "oui",
  Non = "non",
}

export type ReponsesEligibilite = {
  dateDecision?: string;
  actionContentieuse?: ActionContentieuse;
  typeDecision?: TypeDecision;
  piecesProc: PieceProcedure[];
  preuvesDiligences?: PreuvesDiligences;
};

export type StepProps = {
  reponses: ReponsesEligibilite;
  set: <K extends keyof ReponsesEligibilite>(key: K, val: ReponsesEligibilite[K]) => void;
  togglePiece: (val: PieceProcedure) => void;
};
