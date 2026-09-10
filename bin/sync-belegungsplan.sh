#!/usr/bin/env bash
# Synchronisiert den lokalen Arbeitsstand in die Remote-Testinstallation unter
# REMOTE_URL (siehe testinstall.env) und aktualisiert dort Autoloader, Cache
# und Assets. Setzt voraus, dass bin/remote-install.sh bereits einmal gelaufen
# ist (Path-Repository "belegungsplan" ist eingetragen).
#
# Aufruf: bin/sync-belegungsplan.sh [--migrate] [--contao-version=5.3|5.7|6.0]
#   --migrate               zusätzlich contao:migrate ausführen (nach DCA-Änderungen nötig)
#   --contao-version=X.Y    contao/core-bundle vor dem Sync auf dieser Instanz auf
#                            "X.Y.*" umstellen (Composer update) - fuer den Wechsel
#                            zwischen den Kompatibilitaetstests (5.3 <-> 6.0).
#                            Contao 6 benoetigt PHP >=8.4 auf dem Server (REMOTE_PHP
#                            entsprechend in testinstall.env anpassen).
#                            VOR jedem Versionswechsel ein DB-Backup ziehen - ein
#                            Downgrade 6.0 -> 5.3 wird von Contao nicht unterstuetzt.
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

DO_MIGRATE=0
CONTAO_VERSION=""
for arg in "$@"; do
  case "$arg" in
    --migrate) DO_MIGRATE=1 ;;
    --contao-version=*) CONTAO_VERSION="${arg#--contao-version=}" ;;
    *) echo "Unbekannte Option: $arg" >&2; exit 1 ;;
  esac
done

if [ -n "${REMOTE_KEY:-}" ]; then
  RSH="ssh -i ${REMOTE_KEY} -p ${PORT} -o StrictHostKeyChecking=accept-new"
else
  export SSHPASS="${REMOTE_PASSWORD}"
  RSH="sshpass -e ssh -p ${PORT} -o StrictHostKeyChecking=accept-new"
fi

echo "==> Dateien übertragen nach ${BUNDLE_DIR}"
rsync -az --delete -e "${RSH}" \
  --exclude='.git' \
  --exclude='.claude' \
  --exclude='vendor' \
  --exclude='testinstall.env' \
  --exclude='composer.lock' \
  "${HERE}/" "${TARGET}:${BUNDLE_DIR}/"

if [ -n "${CONTAO_VERSION}" ]; then
  echo "==> contao/core-bundle auf ${CONTAO_VERSION}.* umstellen (vorher DB-Backup empfohlen!)"
  ${RSH} "${TARGET}" "export P='${REMOTE_PATH}' PHPBIN='${PHPBIN}' CMP='${COMPOSER}' V='${CONTAO_VERSION}'; bash -s" <<'REMOTE'
set -e
cd "$P"
COMPOSER_BIN="$(command -v "$CMP" || echo "$CMP")"
"$PHPBIN" "$COMPOSER_BIN" require "contao/core-bundle:${V}.*" --no-interaction --no-progress --with-all-dependencies
REMOTE
fi

echo "==> Composer-Autoloader, Cache und Assets auf dem Server aktualisieren"
${RSH} "${TARGET}" "export P='${REMOTE_PATH}' PHPBIN='${PHPBIN}' CMP='${COMPOSER}' MIGRATE='${DO_MIGRATE}'; bash -s" <<'REMOTE'
set -e
cd "$P"
COMPOSER_BIN="$(command -v "$CMP" || echo "$CMP")"

"$PHPBIN" "$COMPOSER_BIN" dump-autoload

# Cache VOR der Migration leeren: contao:migrate liest die DCA-Definitionen aus
# dem Cache. Mit einem alten Cache kennt es neue Felder noch nicht und legt die
# zugehoerigen Spalten nicht an.
"$PHPBIN" vendor/bin/contao-console cache:clear

# Twig-Syntax pruefen, bevor migriert wird
"$PHPBIN" vendor/bin/contao-console lint:twig bundles/belegungsplan-bundle/contao/templates

if [ "$MIGRATE" = "1" ]; then
  "$PHPBIN" vendor/bin/contao-console contao:migrate --no-interaction
fi

"$PHPBIN" vendor/bin/contao-console assets:install
REMOTE

echo "Bundle synchronisiert. Test-Installation: ${REMOTE_URL:-<REMOTE_URL nicht gesetzt>}"
