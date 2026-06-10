import { ActionContentieuse, TypeDecision } from "@/apps/public/components/types";
import { calculerPrescription } from "@/apps/public/services/prescription";

export type CritereEligibilite = {
  label: string;
  rempli: boolean;
  detail: string;
};

type CritereKey =
  | "prescription"
  | "procedureTerminee"
  | "actionContentieuse"
  | "decisionsJustice"
  | "documentsProc"
  | "diligences";

const KEY = "eligibilite_criteres";

const ORDRE: CritereKey[] = [
  "prescription",
  "procedureTerminee",
  "actionContentieuse",
  "decisionsJustice",
  "documentsProc",
  "diligences",
];

// --- Fonctions de construction des critères ---

export function criterePrescription(dateDecision: Date): CritereEligibilite {
  const { rempli, detail } = calculerPrescription(dateDecision);
  return { label: "Prescription", rempli, detail };
}

export function critereProcedureTerminee(): CritereEligibilite {
  return { label: "Procédure terminée", rempli: true, detail: "La procédure est terminée" };
}

const DETAIL_ACTION_CONTENTIEUSE: Record<ActionContentieuse, string> = {
  [ActionContentieuse.Non]: "Non, aucune action contentieuse",
  [ActionContentieuse.Oui]: "Oui, la procédure est en cours devant l'AJE",
};

export function critereActionContentieuse(value: ActionContentieuse): CritereEligibilite {
  return {
    label: "Action contentieuse",
    rempli: value === ActionContentieuse.Non,
    detail: DETAIL_ACTION_CONTENTIEUSE[value],
  };
}

const DETAIL_TYPE_DECISION: Record<TypeDecision, string> = {
  [TypeDecision.JugementPremiereInstance]: "Oui, jugement de première instance",
  [TypeDecision.ArretCourAppel]: "Oui, arrêt de la Cour d'appel",
  [TypeDecision.ArretCourCassation]: "Oui, arrêt de la Cour de cassation",
  [TypeDecision.Aucune]: "Non, il me manque des pièces",
};

export function critereDecisionsJustice(value: TypeDecision): CritereEligibilite {
  return {
    label: "Décisions de justice",
    rempli: value !== TypeDecision.Aucune,
    detail: DETAIL_TYPE_DECISION[value],
  };
}

export function critereDocumentsProc(): CritereEligibilite {
  return { label: "Documents de procédure", rempli: true, detail: "Oui, je dispose des pièces" };
}

export function critereDiligences(preuves: boolean): CritereEligibilite {
  return {
    label: "Diligences accomplies",
    rempli: preuves,
    detail: preuves ? "Oui, j'ai des preuves" : "Non, je n'ai pas de preuves",
  };
}

// --- Store sessionStorage ---

export function saveCritere(key: CritereKey, critere: CritereEligibilite): void {
  const store = getStore();
  store[key] = critere;
  sessionStorage.setItem(KEY, JSON.stringify(store));
}

export function getCriteres(): CritereEligibilite[] {
  const store = getStore();
  return ORDRE.map((k) => store[k]).filter((c): c is CritereEligibilite => c !== undefined);
}

export function clearCriteres(): void {
  sessionStorage.removeItem(KEY);
}

function getStore(): Partial<Record<CritereKey, CritereEligibilite>> {
  try {
    return JSON.parse(sessionStorage.getItem(KEY) ?? "{}");
  } catch {
    return {};
  }
}
