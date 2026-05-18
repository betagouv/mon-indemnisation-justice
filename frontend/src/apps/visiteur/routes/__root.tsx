import { Alert } from "@codegouvfr/react-dsfr/Alert";
import { createRootRoute, Outlet } from "@tanstack/react-router";
import React from "react";

class ErrorBoundary extends React.Component<
  { children: React.ReactNode },
  { hasError: boolean }
> {
  constructor(props: { children: React.ReactNode }) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(): { hasError: boolean } {
    return { hasError: true };
  }

  render() {
    if (this.state.hasError) {
      return (
        <main className="fr-container fr-mt-4w">
          <Alert
            severity="error"
            title="Une erreur inattendue s'est produite"
            description="Veuillez recharger la page ou réessayer ultérieurement."
          />
        </main>
      );
    }
    return this.props.children;
  }
}

export const Route = createRootRoute({
  component: () => (
    <ErrorBoundary>
      <Outlet />
    </ErrorBoundary>
  ),
});
