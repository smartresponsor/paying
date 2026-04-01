#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../.."

bash tools/runtime/payment_docker_prepare.sh

trap 'exit 0' INT TERM

while true; do
  sleep 3600
done
