import { useOnClickOutside } from "@/apps/requerant/hooks/useClickOutside.ts";
import classes from "@/apps/requerant/style/composants/SuggestedInput.module.css";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { InputProps } from "@codegouvfr/react-dsfr/src/Input.tsx";
import * as Sentry from "@sentry/browser";
import { debounce } from "lodash";
import React, {
  ChangeEvent,
  ChangeEventHandler,
  FocusEventHandler,
  ReactNode,
  useRef,
  useState,
} from "react";

/**
 * La suggestion est affichée dans un menu déroulant par son libellé, mais est
 * associé à une valeur dont le type est libre.
 */
type Suggestion<T> = {
  libelle: string;
  valeur: T;
};

/**
 * Dans le choix de la liste de suggestions fixe, l'utilisateur peut également
 * définir la méthode de filtre
 */
type StaticSuggestedInputProps<T> = {
  // En cas de liste de suggestion fixes
  suggestions: Suggestion<T>[];
  // Comment on filtre les valeurs à afficher ? Si omis, c'est la méthode `String.includes` qui sera utilisée
  filtre?: (suggestion: T) => boolean;
  rafraichisseur: never;
  rafraichisseurDebounceMs: never;
  estARafraichir: never;
};

/**
 * Dans le choix de la liste automatiquement rafraichie, l'utilisateur doit
 * fournir une fonction asynchrone `rafraichisseur` qui sera déclenchée dès lors
 * que la valeur du champ teste associé change, et sous réserve de satisfaire une
 * éventuelle fonction de test `estARafraichir`.
 *
 * L'exécution de cette fonction de rafraichissement est _debounced_ d'un délai
 * de `rafraichisseurDebounceMs` si la valeur est définie ou, à défaut, de 500 ms.
 */
type DynamicSuggestedInputProps<T> = {
  suggestions?: never;
  filtre?: never;
  rafraichisseur?: (valeur: string) => Promise<Suggestion<T>[]>;
  rafraichisseurDebounceMs?: number;
  estARafraichir?: (valeur: string) => boolean;
};

type CoreSuggestedInputProps<T> = {
  /** La fonction de retour `onSelectionne` permet à l'utilisateur
   * de définir une logique personnalisée lorsqu'une suggestion est sélectionnée.
   *
   * Retourne la valeur à affecter au champ texte qui, à défaut, sera le libellé
   * de la suggestion sélectionnée.
   *
   * @param suggestion
   */
  onSelectionne: (suggestion: T) => string | void;
} & (StaticSuggestedInputProps<T> | DynamicSuggestedInputProps<T>);

/**
 * Afin de permettre l'abstraction du composant React réellement embarqué, le
 * composant de base n'exige qu'une fonction de _rendering_ à laquelle on impose
 * 3 fonctions de réaction aux évènements `onFocus`, `onBlur` et `onChange` qui
 * **doivent** impérativement être propagés à l'élément qui sera généré.
 *
 * La volonté ici est de permettre à l'utilisateur de personnaliser l'implémentation
 * avec le champ de son choix, comme par exemple, `CheckInput` ou `FormInput`.
 */
export type BaseSuggestedInputProps<TSuggestion extends {} = {}> = {
  renderInput: (props: {
    onFocus: FocusEventHandler<HTMLInputElement>;
    onBlur: FocusEventHandler<HTMLInputElement>;
    onChange: ChangeEventHandler<HTMLInputElement>;
  }) => ReactNode;
} & CoreSuggestedInputProps<TSuggestion>;

/**
 * Le composant d'abstraction qui englobe la logique de rafraîchissement et d'affichage
 * des suggestions ainsi que la réaction et le déclenchement des évènements.
 *
 * @param props
 * @constructor
 */
export const BaseSuggestedInput = <TSuggestion extends {} = {}>({
  ...props
}: BaseSuggestedInputProps<TSuggestion>) => {
  const {
    onSelectionne,
    suggestions,
    filtre,
    rafraichisseur,
    rafraichisseurDebounceMs,
    estARafraichir,
    renderInput,
  } = props;

  // Référence vers le conteneur HTML
  const refConteneur = useRef<HTMLDivElement>(null!);
  // État de l'activité du champ `input`, basé sur les évènements `onFocus` et `onBlur`
  const [estChampActif, setChampActif] = useState(false);

  // La liste des suggestions correspondantes au filtre sur la valeur du champ texte
  const [correspondances, setCorrespondances] = useState<
    Suggestion<TSuggestion>[]
  >(suggestions ?? []);

  // Routine de rafraichissement du menu déroulant, une fois le filtre des
  // suggestions correspondantes effectué
  const rafraichirMenu = (correspondances: Suggestion<TSuggestion>[]) => {
    setCorrespondances(correspondances);
    setAfficherSuggestions(!!correspondances.length);
  };

  // Fonction _debounced_ qui calcule les suggestions correspondantes
  const calculerCorrespondances = rafraichisseur
    ? debounce(
        async (valeur: string) => {
          try {
            const correspondances = await rafraichisseur(valeur);
            rafraichirMenu(correspondances);
          } catch (e) {
            if (import.meta.env.DEV) console.log(e);
            Sentry.captureException(e);
          }
        },
        rafraichisseurDebounceMs || 500,
        { trailing: true },
      )
    : undefined;

  // Est-ce que les suggestions doivent être affichées
  const [afficherSuggestions, setAfficherSuggestions] = useState(false);

  // Détecter un clic en dehors du menu déroulant des suggestions
  useOnClickOutside(
    refConteneur,
    () => setAfficherSuggestions(false),
    estChampActif,
  );

  // Réagir au changement de valeur du champ texte
  const handleInputChange = async (valeur: string) => {
    if (!!calculerCorrespondances) {
      /** On ne déclenche le rafraichissement que selon la décision de la fonction
       * `estARafraichir` sur la valeur actuelle du champ, si elle est définie,
       * automatiquement sinon.
       */
      if (!estARafraichir || estARafraichir(valeur)) {
        calculerCorrespondances(valeur);
      }
    } else {
      rafraichirMenu(
        suggestions?.filter((suggestion) =>
          filtre
            ? filtre(suggestion.valeur)
            : suggestion.libelle.includes(valeur),
        ) || [],
      );
    }
  };

  // Réagir au clic sur une des suggestions
  const handleSelectOption = (suggestion: Suggestion<TSuggestion>) => {
    const input = refConteneur.current?.querySelector("input");

    if (input) {
      input.value = onSelectionne(suggestion.valeur) ?? suggestion.libelle;
    }
    setAfficherSuggestions(false);
  };

  return (
    <div className={classes.suggestedInput} ref={refConteneur}>
      {renderInput({
        onFocus: (e) => {
          setChampActif(false);
          setAfficherSuggestions(false);
        },
        onBlur: (e) => setChampActif(false),
        onChange: (e: ChangeEvent<HTMLInputElement>) => {
          handleInputChange(e.target.value);
        },
      })}
      {afficherSuggestions && (
        <ul>
          {correspondances.map((suggestion, index) => (
            <li key={index} onClick={() => handleSelectOption(suggestion)}>
              {suggestion.libelle}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
};

type SuggestedInputProps<TSuggestion extends {} = {}> = Omit<
  BaseSuggestedInputProps<TSuggestion>,
  "renderInput"
> &
  Omit<InputProps, "textArea" | "nativeTextAreaProps">;

/**
 * Implémentation du composant de base de suggestion `BaseSuggestedInput` avec le
 * champ texte du DSFR `Input`, que l'on contraint à ne pas être de type `textarea`.
 *
 * @param onSelectionne
 * @param suggestions
 * @param filtre
 * @param rafraichisseur
 * @param rafraichisseurDebounceMs
 * @param estARafraichir
 * @param nativeInputProps
 * @param props
 * @constructor
 */
export const SuggestedInput = <TSuggestion extends {} = {}>({
  onSelectionne,
  suggestions,
  filtre,
  rafraichisseur,
  rafraichisseurDebounceMs,
  estARafraichir,
  nativeInputProps,
  ...inputProps
}: SuggestedInputProps<TSuggestion>) => {
  return (
    <BaseSuggestedInput
      renderInput={({ onFocus, onBlur, onChange }) => (
        <Input
          nativeInputProps={{
            ...nativeInputProps,
            onFocus: (e) => {
              onFocus(e);
              nativeInputProps?.onFocus?.(e);
            },
            onBlur: (e) => {
              onBlur(e);
              nativeInputProps?.onBlur?.(e);
            },
            onChange: (e) => {
              onChange(e);
              nativeInputProps?.onChange?.(e);
            },
          }}
          {...inputProps}
        />
      )}
      {...({
        onSelectionne,
        suggestions,
        rafraichisseur,
        rafraichisseurDebounceMs,
        estARafraichir,
      } as any)}
    />
  );
};
