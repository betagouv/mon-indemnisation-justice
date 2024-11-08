// import Sentry from your framework SDK (e.g. @sentry/react) instead of @sentry/browser
import * as Sentry from "@sentry/browser";

if (import.meta.env?.VITE_SENTRY_DSN) {
  Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,

    integrations: [
      Sentry.feedbackIntegration({
        // Additional SDK configuration goes in here, for example:
        colorScheme: "system",
        showEmail: false,
        isNameRequired: true,
        formTitle: "Faire un retour",
        nameLabel: "Prénom",
        namePlaceholder: "Votre prénom",
        isRequiredLabel: "(requis)",
        messageLabel: "Description du problème",
        messagePlaceholder: "Coquille sur le titre \"Machin\" / Le bouton \"Valider\" ne marche pas",
        triggerLabel: "Remonter une erreur / coquille",
        addScreenshotButtonLabel: "Faire une capture d'écran",
        submitButtonLabel: "Remonter",
        cancelButtonLabel: "Annuler",
        confirmButtonLabel: "Confirmer",
        successMessageText: "C'est transmis à l'équipe tech!"
      }),
    ],
  });
}
