import React, {
  type ClipboardEvent,
  type KeyboardEvent,
  type ReactNode,
  useId,
  useLayoutEffect,
  useRef,
} from "react";
import { Requis } from "@/common/composants/dsfr/Requis.tsx";
import {
  type PositionCurseur,
  repartirSaisie,
} from "@common/composants/dsfr/champs/segments.ts";

export type Segment = {
  label: ReactNode;
  longueur: number;
  inputMode?: "numeric" | "text";
};

export type ChampsSegmentesProps = {
  legende: ReactNode;
  description?: ReactNode;
  segments: Segment[];
  valeurs: string[];
  onChange: (valeurs: string[]) => void;
  onBlur?: () => void;
  /** Ne conserve que les caractères autorisés (par défaut : retire les espaces et passe en majuscules) */
  filtrer?: (saisie: string) => string;
  estRequis?: boolean;
  /** Message d'erreur affiché sous les champs */
  erreur?: ReactNode;
};

const filtreParDefaut = (saisie: string) =>
  saisie.replace(/\s+/g, "").toUpperCase();

/**
 * Plusieurs champs de longueur fixe se comportant comme une seule zone de saisie (ex : les 4 champs d'un RIB) :
 * - une valeur collée ou tapée au-delà de la longueur d'un champ déborde sur les suivants ;
 * - un champ rempli fait passer au suivant, et effacer le début d'un champ (ou le vider) revient au précédent ;
 * - les flèches gauche / droite passent d'un champ à l'autre aux extrémités.
 *
 * @see repartirSaisie pour les règles de répartition
 */
export const ChampsSegmentes = ({
  legende,
  description,
  segments,
  valeurs,
  onChange,
  onBlur,
  filtrer = filtreParDefaut,
  estRequis = false,
  erreur,
}: ChampsSegmentesProps) => {
  const id = useId();
  const champs = useRef<(HTMLInputElement | null)[]>([]);
  // Curseur à positionner une fois les nouvelles valeurs rendues
  const curseurEnAttente = useRef<PositionCurseur | null>(null);
  const longueurs = segments.map((segment) => segment.longueur);

  const positionner = ({ index, position }: PositionCurseur) => {
    const champ = champs.current[index];
    champ?.focus();
    champ?.setSelectionRange(position, position);
  };

  useLayoutEffect(() => {
    if (curseurEnAttente.current) {
      positionner(curseurEnAttente.current);
      curseurEnAttente.current = null;
    }
  });

  /** Positionne le curseur une fois les valeurs modifiées rendues */
  const deplacer = (curseur: PositionCurseur) => {
    curseurEnAttente.current = curseur;
  };

  const saisir = (
    index: number,
    saisie: string,
    curseurBrut: number,
    estSuppression: boolean,
  ) => {
    // Le curseur est recalculé sur la saisie filtrée (espaces retirés notamment)
    const curseur = filtrer(saisie.slice(0, curseurBrut)).length;
    const resultat = repartirSaisie(
      valeurs,
      longueurs,
      index,
      filtrer(saisie),
      curseur,
    );

    // Vider un champ en effaçant renvoie à la fin du champ précédent
    deplacer(
      estSuppression && resultat.valeurs[index] === "" && index > 0
        ? { index: index - 1, position: resultat.valeurs[index - 1].length }
        : resultat.curseur,
    );
    onChange(resultat.valeurs);
  };

  const gererTouche = (index: number, e: KeyboardEvent<HTMLInputElement>) => {
    const champ = e.currentTarget;
    const auDebut = champ.selectionStart === 0 && champ.selectionEnd === 0;
    const aLaFin =
      champ.selectionStart === champ.value.length &&
      champ.selectionEnd === champ.value.length;

    if (e.key === "Backspace" && auDebut && index > 0) {
      // Effacer au début d'un champ efface le dernier caractère du précédent
      e.preventDefault();
      const precedent = valeurs[index - 1];
      const nouvellesValeurs = [...valeurs];
      nouvellesValeurs[index - 1] = precedent.slice(0, -1);
      deplacer({
        index: index - 1,
        position: Math.max(0, precedent.length - 1),
      });
      onChange(nouvellesValeurs);
    } else if (e.key === "ArrowLeft" && auDebut && index > 0) {
      e.preventDefault();
      positionner({ index: index - 1, position: valeurs[index - 1].length });
    } else if (
      e.key === "ArrowRight" &&
      aLaFin &&
      index + 1 < segments.length
    ) {
      e.preventDefault();
      positionner({ index: index + 1, position: 0 });
    }
  };

  const coller = (index: number, e: ClipboardEvent<HTMLInputElement>) => {
    // Le collage est géré à la main pour ignorer les espaces et séparateurs de la valeur collée
    e.preventDefault();
    const champ = e.currentTarget;
    const debut = champ.selectionStart ?? champ.value.length;
    const fin = champ.selectionEnd ?? champ.value.length;
    const colle = filtrer(e.clipboardData.getData("text"));
    const saisie = champ.value.slice(0, debut) + colle + champ.value.slice(fin);

    saisir(index, saisie, debut + colle.length, false);
  };

  return (
    <fieldset
      className={`fr-fieldset ${erreur ? "fr-fieldset--error" : ""}`}
      id={`${id}-fieldset`}
      role="group"
      aria-labelledby={`${id}-legende ${erreur ? `${id}-messages` : ""}`}
    >
      <legend className="fr-fieldset__legend" id={`${id}-legende`}>
        {legende}
        {estRequis && <Requis />}
        {description && <span className="fr-hint-text">{description}</span>}
      </legend>

      {segments.map((segment, index) => (
        <div
          key={index}
          className="fr-fieldset__element fr-fieldset__element--inline"
          style={{
            width: `calc(${segment.longueur + 1}ch + 2rem)`,
            minWidth: "5rem",
          }}
        >
          <div
            className={`fr-input-group ${erreur ? "fr-input-group--error" : ""}`}
          >
            <label className="fr-label" htmlFor={`${id}-${index}`}>
              {segment.label}
            </label>
            <input
              ref={(element) => {
                champs.current[index] = element;
              }}
              className={`fr-input ${erreur ? "fr-input--error" : ""}`}
              id={`${id}-${index}`}
              type="text"
              inputMode={segment.inputMode ?? "text"}
              autoComplete="off"
              spellCheck={false}
              value={valeurs[index] ?? ""}
              onChange={(e) =>
                saisir(
                  index,
                  e.target.value,
                  e.target.selectionStart ?? e.target.value.length,
                  e.target.value.length < (valeurs[index] ?? "").length,
                )
              }
              onKeyDown={(e) => gererTouche(index, e)}
              onPaste={(e) => coller(index, e)}
              onBlur={onBlur}
            />
          </div>
        </div>
      ))}

      <div
        className="fr-messages-group"
        id={`${id}-messages`}
        aria-live="polite"
      >
        {erreur && <p className="fr-message fr-message--error">{erreur}</p>}
      </div>
    </fieldset>
  );
};
