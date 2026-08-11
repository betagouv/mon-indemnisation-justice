import { RadioButtons } from "@codegouvfr/react-dsfr/RadioButtons";
import React, { useEffect, useRef } from "react";

type Option<T extends string> = {
  label: string;
  valeur: T;
};

type QuestionChoixProps<T extends string> = {
  legend: string;
  options: [Option<T>, Option<T>];
  valeur?: T;
  onReponse: (valeur: T) => void;
  autoScroll?: boolean;
};

export function QuestionChoix<T extends string>({
  legend,
  options,
  valeur,
  onReponse,
  autoScroll = true,
}: QuestionChoixProps<T>) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (autoScroll) {
      ref.current?.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
    // Ne défiler qu'à l'apparition de la question, pas à chaque changement de réponse
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <div ref={ref}>
      <RadioButtons
        legend={legend}
        options={options.map((option) => ({
          label: option.label,
          nativeInputProps: {
            checked: valeur === option.valeur,
            onChange: () => onReponse(option.valeur),
          },
        }))}
      />
    </div>
  );
}
