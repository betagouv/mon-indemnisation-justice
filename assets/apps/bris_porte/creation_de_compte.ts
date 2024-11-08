import {createApp} from 'petite-vue';

const inscription = Object.assign(
    {
        prenom: "",
        nom: "",
        nomNaissance: "",
        courriel: "",
        motDePasse: "",
        confirmation: "",
        cguOk: true
    },
    JSON.parse(document.getElementById('input-inscription')?.textContent || "{}")
);
const erreurs = Object.assign({}, JSON.parse(document.getElementById('input-erreurs')?.textContent || "{}"));

createApp({
    erreurs: {...erreurs},
    submissible: false,
    inscription: {...inscription},
    avant: {},
    revelations: {
        motDePasse: false,
        confirmation: false,
    },
    estRempli(valeur) {
        return !!valeur && valeur.trim().length > 0;
    },
    estCourrielValide(valeur) {
        return !!valeur && valeur.match(/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    },
    basculer(champs) {
        this.revelations[champs] = !this.revelations[champs];
    },
    verifier(inscription) {
        if (this.avant?.prenom !== inscription.prenom) {
            if (!this.estRempli(inscription.prenom)) {
                this.erreurs.prenom = "";
            } else {
                delete this.erreurs.prenom;
            }
        }

        if (this.avant?.nom !== inscription.nom) {
            if (!this.estRempli(inscription.nom)) {
                this.erreurs.nom = "";
            } else {
                delete this.erreurs.nom;
            }
        }

        if (this.avant?.courriel !== inscription.courriel) {
            if (!this.estCourrielValide(inscription.courriel)) {
                this.erreurs.courriel = inscription.courriel ? "L'adresse courriel n'est pas valide" : false;
            } else {
                delete this.erreurs.courriel;
            }
        }

        // Vérification du mot de passe:
        if ((inscription.motDePasse || null) !== null) {
            if (
                (inscription.motDePasse != null && this.avant?.motDePasse !== inscription.motDePasse) ||
                (this.avant?.confirmation !== inscription.confirmation)
            ) {
                delete this.erreurs.motDePasse;
                delete this.erreurs.confirmation;
                if (inscription.motDePasse.length < 8) {
                    this.erreurs.motDePasse = "Le mot de passe doit contenir au moins 8 caractères, dont 1 chiffre";
                } else if (!inscription.motDePasse.match(/\d/)) {
                    this.erreurs.motDePasse = "Le mot de passe doit contenir au moins 1 chiffre";
                } else if (this.estRempli(inscription.motDePasse) && inscription.confirmation !== inscription.motDePasse) {
                    this.erreurs.confirmation = "Les deux mots de passe doivent être identiques";
                }
            }
        }


        /*
        if (this.avant?.cguOk !== inscription.cguOk) {
            if (!inscription.cguOk) {
                this.erreurs.cguOk = "";
            } else {
                delete this.erreurs.cguOk;
            }
        }
        */

        this.submissible = Object.keys(this.erreurs).length === 0;

        this.avant = {...inscription};
    },
}).mount('#vue-app');