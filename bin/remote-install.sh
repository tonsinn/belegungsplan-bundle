#!/usr/bin/env bash
# Installiert das belegungsplan-bundle erstmalig auf dem Remote-Testserver aus dem
# lokalen Arbeitsstand. Das Bundle wird als Composer-Path-Repository unter
# $REMOTE_PATH/bundles/belegungsplan-bundle eingebunden.
#
# ACHTUNG: Das Path-Repository ist canonical und hat Vorrang vor Packagist.
# Solange es eingetragen ist, laesst sich die Instanz NICHT auf eine
# veroeffentlichte Version (z. B. ^5.0) umstellen - Composer bricht mit
# "has higher repository priority" ab. Zum Wechsel auf die Release-Version:
#   composer config --unset repositories.belegungsplan
#   composer require tonsinn/belegungsplan-bundle:^5.0
# bin/remote-uninstall.sh macht die Umstellung wieder rueckgaengig.
#
# Fuer laufende Aenderungen nach der Erstinstallation bin/sync-belegungsplan.sh
# verwenden - das ist deutlich schneller, da es nur rsync + Cache/Assets macht.
#
# Zugangsdaten: testinstall.env (siehe testinstall.env.example)
set -euo pipefail

HERE="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="${HERE}/testinstall.env"

[ -f "${ENV_FILE}" ] || { echo "testinstall.env fehlt (Vorlage: testinstall.env.example)"; exit 1; }
set -a; . "${ENV_FILE}"; set +a

PORT="${REMOTE_PORT:-22}"
TARGET="${REMOTE_USER}@${REMOTE_HOST}"
BUNDLE_DIR="${REMOTE_PATH}/bundles/belegungsplan-bundle"
PHPBIN="${REMOTE_PHP:-keyhelp-php84}"
COMPOSER="${REMOTE_COMPOSER:-composer}"

if [ -n "${REMOTE_KEY:-}" ]; then
  RSH="ssh -i ${REMOTE_KEY} -p ${PORT} -o StrictHostKeyChecking=accept-new"
else
  export SSHPASS="${REMOTE_PASSWORD}"
  RSH="sshpass -e ssh -p ${PORT} -o StrictHostKeyChecking=accept-new"
fi

echo "==> PHP-Version auf dem Server prüfen"
${RSH} "${TARGET}" "export PHPBIN='${PHPBIN}'; bash -s" <<'REMOTE'
if ! command -v "$PHPBIN" >/dev/null 2>&1; then
  echo "FEHLER: Keine PHP-Binary gefunden ('$PHPBIN'). REMOTE_PHP in testinstall.env pruefen." >&2
  exit 1
else
  "$PHPBIN" -v | head -1
fi
REMOTE

echo "==> Dateien übertragen nach ${BUNDLE_DIR}"
${RSH} "${TARGET}" "mkdir -p '${BUNDLE_DIR}'"
rsync -az --delete -e "${RSH}" \
  --exclude='.git' \
  --exclude='.claude' \
  --exclude='vendor' \
  --exclude='testinstall.env' \
  --exclude='composer.lock' \
  "${HERE}/" "${TARGET}:${BUNDLE_DIR}/"

echo "==> Composer und Contao-Migration auf dem Server"
${RSH} "${TARGET}" "export P='${REMOTE_PATH}' PHPBIN='${PHPBIN}' CMP='${COMPOSER}'; bash -s" <<'REMOTE'
set -e
cd "$P"

COMPOSER_BIN="$(command -v "$CMP" || echo "$CMP")"

"$PHPBIN" "$COMPOSER_BIN" config repositories.belegungsplan \
  '{"type":"path","url":"bundles/belegungsplan-bundle","options":{"symlink":true,"versions":{"tonsinn/belegungsplan-bundle":"dev-main"}}}'
"$PHPBIN" "$COMPOSER_BIN" require tonsinn/belegungsplan-bundle:@dev --no-interaction --no-progress

# Cache VOR der Migration leeren: contao:migrate liest die DCA-Definitionen aus
# dem Cache. Mit einem alten Cache kennt es neue Felder noch nicht und legt die
# zugehoerigen Spalten nicht an.
"$PHPBIN" vendor/bin/contao-console cache:clear

# Twig-Syntax pruefen, bevor migriert wird: ein Fehler im Template legt sonst
# das Frontend lahm und faellt erst beim Aufruf auf.
"$PHPBIN" vendor/bin/contao-console lint:twig bundles/belegungsplan-bundle/contao/templates

"$PHPBIN" vendor/bin/contao-console contao:migrate --no-interaction
"$PHPBIN" vendor/bin/contao-console assets:install
REMOTE

echo "Fertig. Test-Installation: ${REMOTE_URL:-<REMOTE_URL nicht gesetzt>}"
