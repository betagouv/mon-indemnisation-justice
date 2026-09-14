import {
  ActionContentieuse,
  TypeDecision,
} from "@/apps/public/components/types";
import {
  LibellesPreuveDiligence,
  TypePreuveDiligence,
} from "@/apps/public/models/TestEligibilite.ts";
import {
  calculerDateFin,
  calculerPrescription,
} from "@/apps/public/services/prescription";
import { dateSimple } from "@common/services/date.ts";

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
  const datePrescription = calculerPrescription(dateDecision);
  const dateFin = calculerDateFin(datePrescription);
  const estRempli = new Date() < (datePrescription as Date);
  return {
    label: "Prescription",
    rempli: estRempli,
    detail: estRempli
      ? `Vous pouvez présenter votre demande jusqu’au ${dateSimple(dateFin as Date)} inclus.`
      : `Le délai pour présenter votre demande a expiré le  ${dateSimple(datePrescription as Date)}.`,
  };
}

export function critereProcedureTerminee(): CritereEligibilite {
  return {
    label: "Procédure terminée",
    rempli: true,
    detail: "La procédure est terminée",
  };
}

const DETAIL_ACTION_CONTENTIEUSE: Record<ActionContentieuse, string> = {
  [ActionContentieuse.Non]: "Non, aucune action contentieuse",
  [ActionContentieuse.Oui]: "Oui, la procédure est en cours devant l'AJE",
};

export function critereActionContentieuse(
  value: ActionContentieuse,
): CritereEligibilite {
  return {
    label: "Action contentieuse",
    rempli: value === ActionContentieuse.Non,
    detail: DETAIL_ACTION_CONTENTIEUSE[value],
  };
}

const DETAIL_TYPE_DECISION: Partial<Record<TypeDecision, string>> = {
  [TypeDecision.JugementPremiereInstance]: "Décision de première instance",
  [TypeDecision.ArretCourAppel]: "Décision de la cour d'appel",
  [TypeDecision.ArretCourCassation]: "Décision de la Cour de cassation",
};

export function critereDecisionsJustice(
  values: TypeDecision[],
): CritereEligibilite {
  const aucune = values.includes(TypeDecision.Aucune);
  return {
    label: "Décisions de justice",
    rempli: !aucune && values.length > 0,
    detail: aucune
      ? "Non, je ne dispose pas des décisions requises"
      : "Oui, je dispose des décisions de justice",
  };
}

export function critereDocumentsProc(): CritereEligibilite {
  return {
    label: "Documents de procédure",
    rempli: true,
    detail: "Oui, je dispose des pièces",
  };
}

export function critereDiligences(
  preuves: TypePreuveDiligence,
): CritereEligibilite {
  return {
    label: "Diligences accomplies",
    rempli: preuves !== "pas_de_demarche",
    detail: LibellesPreuveDiligence[preuves],
  };
}

// --- Store sessionStorage ---

export function saveCritere(
  key: CritereKey,
  critere: CritereEligibilite,
): void {
  const store = getStore();
  store[key] = critere;
  sessionStorage.setItem(KEY, JSON.stringify(store));
}

export function getCriteres(): CritereEligibilite[] {
  const store = getStore();
  return ORDRE.map((k) => store[k]).filter(
    (c): c is CritereEligibilite => c !== undefined,
  );
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
