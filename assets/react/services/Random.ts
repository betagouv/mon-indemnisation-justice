
const alphabet = 'abcdefghijklmnopqrstuvwxyz'
const allowedCharacters = `${alphabet}ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`


function randomPick(value: string): string {
    return value.charAt(Math.floor(Math.random() * (allowedCharacters.length - 1)));
}

function randomString(length: number = 10): string {
    if (length <= 0) {
        return "";
    }
    return randomPick(allowedCharacters).concat(randomString(length - 1));
}

/**
 * Génère un `id` html aléatoire. En HTML4, mais surtout pour Panther PHP, un `id` doit commencer par une lettre.
 *
 * @param length
 */
function randomId(length: number = 10): string {
    if (length <= 0) {
        return "";
    }
    return randomPick(alphabet).concat(randomString(length - 1));
}

export { randomId };
