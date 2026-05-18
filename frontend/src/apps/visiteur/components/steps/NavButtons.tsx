import { Button } from "@codegouvfr/react-dsfr/Button";

type NavButtonsProps = {
  onPrecedent: () => void;
  onSuivant: () => void;
  canContinue: boolean;
  isLastStep: boolean;
};

export function NavButtons({ onPrecedent, onSuivant, canContinue, isLastStep }: NavButtonsProps) {
  return (
    <div className="fr-mt-3w fr-btns-group fr-btns-group--inline">
      <Button
        priority="tertiary no outline"
        iconId="fr-icon-arrow-left-line"
        iconPosition="left"
        onClick={onPrecedent}
      >
        Retour
      </Button>
      <Button disabled={!canContinue} onClick={onSuivant}>
        {isLastStep ? "Voir le résultat" : "Continuer"}
      </Button>
    </div>
  );
}
