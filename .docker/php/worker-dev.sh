#!/usr/bin/env bash
# Worker Messenger de développement : redémarre `messenger:consume` dès que le code PHP change.
#
# Un processus `messenger:consume` garde en mémoire le code chargé à son démarrage : sans redémarrage, les
# modifications ne sont pas prises en compte. On vérifie régulièrement par `find -newer` si un fichier surveillé a été modifié.
#
# L'arrêt passe par `messenger:stop-workers`, qui laisse le worker terminer le message en cours de traitement.

set -u

# Nombre de secondes entre 2 vérifications
INTERVALLE=2
# Liste des chemins à surveiller
CHEMINS=(src config .env .env.dev composer.lock)
MARQUEUR=$(mktemp)

consommer() {
    touch "$MARQUEUR"
    php bin/console messenger:consume async -vv --time-limit=3600 --memory-limit=256M &
    PID=$!
}

code_modifie() {
    local chemins=()
    for chemin in "${CHEMINS[@]}"; do
        [ -e "$chemin" ] && chemins+=("$chemin")
    done
    [ -n "$(find "${chemins[@]}" -newer "$MARQUEUR" -type f -print -quit 2>/dev/null)" ]
}

arreter() {
    php bin/console messenger:stop-workers --no-interaction > /dev/null
    for _ in $(seq 1 30); do
        kill -0 "$PID" 2>/dev/null || return
        sleep 1
    done
    echo "[worker-dev] Le worker ne s'est pas arrêté après 30s, arrêt forcé"
    kill -9 "$PID" 2>/dev/null
}

trap 'arreter; exit 0' TERM INT

consommer

while true; do
    sleep "$INTERVALLE"

    if ! kill -0 "$PID" 2>/dev/null; then
        # Arrêt de lui-même (time-limit, memory-limit, erreur) : on relance
        wait "$PID"
        echo "[worker-dev] Le worker s'est arrêté (code $?), redémarrage"
        consommer
    elif code_modifie; then
        echo "[worker-dev] Modification du code détectée, redémarrage du worker"
        arreter
        wait "$PID" 2>/dev/null
        consommer
    fi
done
