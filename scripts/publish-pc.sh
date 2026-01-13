#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
src_dir="$repo_root/template/pc/dist"
dest_dir="$repo_root/crmeb/public/home"

if [[ ! -d "$src_dir" ]]; then
  echo "PC build output not found: $src_dir" >&2
  echo "Run 'npm run generate' in template/pc first, then re-run this script." >&2
  exit 1
fi

mkdir -p "$dest_dir"

if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete "$src_dir/" "$dest_dir/"
else
  rm -rf "$dest_dir"
  mkdir -p "$dest_dir"
  cp -R "$src_dir/." "$dest_dir/"
fi

echo "Published PC (Nuxt) to: $dest_dir"
