#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DIST_DIR="${ROOT_DIR}/dist"

mkdir -p "${DIST_DIR}"
rm -f "${DIST_DIR}/gayanes-theme.zip" "${DIST_DIR}/gayanes-core.zip"

(cd "${ROOT_DIR}/wp-content/themes" && zip -rq "${DIST_DIR}/gayanes-theme.zip" gayanes-theme -x "*.DS_Store")
(cd "${ROOT_DIR}/wp-content/plugins" && zip -rq "${DIST_DIR}/gayanes-core.zip" gayanes-core -x "*.DS_Store")

echo "Packaged:"
ls -lh "${DIST_DIR}/gayanes-theme.zip" "${DIST_DIR}/gayanes-core.zip"

