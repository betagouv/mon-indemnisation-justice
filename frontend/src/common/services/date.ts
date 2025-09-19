export const periode = (de: Date, a: Date = new Date()): string => {
    // Différence en secondes
    let diff = (a.getTime() - de.getTime()) / 1000;

    if (diff < 60) {
        return "moins d'une minute";
    }

    diff = diff / 60;

    // Différence en minutes
    if (diff < 60) {
        return `${Math.floor(diff)} minute${Math.floor(diff) > 1 ? "s" : ""}`;
    }

    // Différence en heures
    diff = diff / 60;

    if (diff < 24) {
        return `${Math.floor(diff)} heure${Math.floor(diff) > 1 ? "s" : ""}`;
    }

    // Différence en jours
    diff = diff / 24;

    if (diff < 30) {
        return `${Math.floor(diff)} jour${Math.floor(diff) > 1 ? "s" : ""}`;
    }

    if (diff < 365) {
        // Différence en mois (~30 jours)
        return `${Math.floor(diff / 30)} mois`;
    }

    // Différence en années
    diff = diff / 365;

    return `${Math.floor(diff)} an${Math.floor(diff) > 1 ? "s" : ""}`;
};

export const dateSimple = function (date: Date, masquerAnneeSiCourante: boolean = false): string {
    return date.toLocaleString("fr-FR", {
        day: "numeric",
        month: "long",
        year:
            masquerAnneeSiCourante && date.getFullYear() ===
            new Date().getFullYear()
                ? undefined
                : "numeric",
    });
}
