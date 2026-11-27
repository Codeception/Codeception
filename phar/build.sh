#!/usr/bin/env bash
# Builds codecept.phar for a release tag: phar/build.sh 5.3.7
#
# The Codeception sources are taken from `git archive <tag>`, so export-ignore rules apply
# and the phar contains the tagged code even when this script runs on another branch.
# Result: phar/build/codecept.phar
set -euo pipefail

BOX_VERSION=4.7.1

TAG=${1:-}
if [[ ! "$TAG" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Usage: $0 <release tag, e.g. 5.3.7>" >&2
    exit 1
fi

PHAR_DIR=$(cd "$(dirname "$0")" && pwd)
ROOT_DIR=$(dirname "$PHAR_DIR")
BUILD_DIR="$PHAR_DIR/build"

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/codeception"
git -c safe.directory="$ROOT_DIR" -C "$ROOT_DIR" archive "$TAG" | tar -x -C "$BUILD_DIR/codeception"
cp "$PHAR_DIR/composer.json" "$PHAR_DIR/box.json" "$PHAR_DIR/stub.php" "$BUILD_DIR/"

cd "$BUILD_DIR"
composer config repositories.codeception "{\"type\": \"path\", \"url\": \"codeception\", \"options\": {\"symlink\": false, \"versions\": {\"codeception/codeception\": \"$TAG\"}}}"
composer require --no-update "codeception/codeception:$TAG"
# TODO: remove once module-ftp allows a phpseclib version without security advisories
COMPOSER_NO_SECURITY_BLOCKING=1 composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress \
    --ignore-platform-req=ext-apcu \
    --ignore-platform-req=ext-mongodb \
    --ignore-platform-req=ext-phalcon

curl -fsSL -o box.phar "https://github.com/box-project/box/releases/download/$BOX_VERSION/box.phar"
php -d phar.readonly=0 box.phar compile --no-interaction

# --no-redirect: otherwise autoload.php runs the copy in ./vendor/codeception instead of the phar
# only the first line: the bundled module versions follow it
php codecept.phar --version --no-redirect | sed -n 1p | grep -F "$TAG"
