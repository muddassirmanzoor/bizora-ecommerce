#!/bin/bash
# Sync with client repo (vestbee): pull their changes, then push yours.
# Run this in your terminal so GitHub can use your credentials.

set -e
cd "$(dirname "$0")"

echo "→ Pulling from vestbee (client) main..."
git pull vestbee main --no-rebase --no-edit

echo "→ Pushing to vestbee (client) main..."
git push vestbee main

echo "✓ Done. vestbee is in sync."
