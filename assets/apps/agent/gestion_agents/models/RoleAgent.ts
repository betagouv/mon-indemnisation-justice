interface RoleAgent {
  libelle: string;
  description: string;
}

interface RoleAgentSet {
  [name: string]: RoleAgent;
}

const RolesAgent: RoleAgentSet = {
  ROLE_AGENT_REDACTEUR: {
    libelle: "Rédacteur",
    description: "Instruit les dossiers d'indemnisation",
  },
  ROLE_AGENT_DOSSIER: {
    libelle: "Consultation",
    description: "Permet de chercher et consulter les dossiers",
  },
  ROLE_AGENT_GESTION_PERSONNEL: {
    libelle: "Gestionnaire d'agent",
    description: "Définit les permissions accordées aux agents",
  },
  ROLE_AGENT_VALIDATEUR: {
    libelle: "Validateur",
    description: "Valide les décisions",
  },
  ROLE_AGENT_ATTRIBUTEUR: {
    libelle: "Attributeur",
    description: "Attribue les dossiers aux rédacteurs",
  },
  ROLE_AGENT_BUREAU_BUDGET: {
    libelle: "Agent du bureau du budget",
    description: "Programme les versements d'indemnisation",
  },
  ROLE_AGENT_FORCES_DE_L_ORDRE: {
    libelle: "Agent des forces de l'ordre",
    description: "Déclare des opérations",
  },
};

export { RolesAgent };
