import { createApp } from 'petite-vue';

class Departement {
    code: string;
    nom: string;
}

const args = JSON.parse(document.getElementById('vue-arguments')?.textContent || "{}");

const departements = (args?.departements.map(({code, nom}) => ({
    code,
    nom: `${code.padStart(2, '0')} - ${nom}`
})) || []) as Departement[];

console.log(departements)

createApp({
    departements,
    reponses: {
        departement: null,
        estVise: null,
        estRecherche: null,
        estProprietaire: null,
        aContacteAssurance: null,
        aContacteBailleur: null,
    },
    questions: {
        estVise: function(reponses, decisions) { return reponses.departement != null; },
        estRecherche: function(reponses, decisions) { return reponses.estVise != null && (decisions.length === 0 || reponses.estRecherche != null); },
        estProprietaire: function(reponses, decisions) { return reponses.estRecherche != null  && (decisions.length === 0 || reponses.estProprietaire != null); },
        aContacteAssurance: function(reponses, decisions) { return reponses.estProprietaire != null && (decisions.length === 0 || reponses.aContacteAssurance != null); },
        aContacteBailleur: function(reponses, decisions) { return reponses.aContacteAssurance != null && reponses.estProprietaire === false  && (decisions.length === 0 || reponses.aContacteBailleur != null); }
    },
    decisions: [],
    estDecide: function (nom) {
        return !!this.decisions.find(function(decision) { return decision.name === nom; });
    },
    estEligible() {
        return this.decisions.every((decision) => decision?.eligible !== false)
    },
    statuer: function (reponses) {
        this.decisions.length = 0;
        // Statuer sur une décision ici :
        if (reponses.estVise) {
            this.decisions.push({
                name: "error_est_vise",
                eligible: false
            });
        } else if (reponses.estRecherche) {
            this.decisions.push({
                name: "error_est_recherche",
                eligible: false
            });
        } else {
            if (reponses.estProprietaire) {
                if (reponses.aContacteAssurance != null) {
                    this.decisions.push({
                        name: reponses.aContacteAssurance ? 'success' : 'warning_proprietaire_assurance'
                    });
                }
            } else if (reponses.estProprietaire === false) {
                if (reponses.aContacteAssurance != null && reponses.aContacteBailleur != null) {
                    if (reponses.aContacteAssurance === false) {
                        this.decisions.push({
                            name: "warning_locataire_assurance"
                        });
                    }
                    if (reponses.aContacteBailleur === false) {
                        this.decisions.push({
                            name: 'warning_locataire_bailleur'
                        });
                    }
                    if (reponses.aContacteAssurance && reponses.aContacteBailleur) {
                        this.decisions.push({
                            name: 'success'
                        });
                    }
                }
            }
        }
    },
    reinitialiser: function (question) {
        if (question) {
            const questions = Object.keys(this.reponses);
            for (const c of questions.slice(questions.indexOf(question) + 1)) {
                this.reponses[c] = null;
            }
            this.statuer(this.reponses);
        } else {
            for (const c of Object.keys(this.reponses)) {
                this.reponses[c] = null;
            }
            this.decisions.length = 0;
        }
    },
}).mount('#vue-app');