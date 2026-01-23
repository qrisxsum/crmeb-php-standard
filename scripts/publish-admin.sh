#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
src_dir="$repo_root/template/admin/dist"
dest_dir="$repo_root/crmeb/public/admin"
backup_root="$repo_root/crmeb/backup/frontend"
backup_enabled="${CRMEB_PUBLISH_BACKUP:-1}"

if [[ ! -d "$src_dir" ]]; then
  echo "Admin build output not found: $src_dir" >&2
  echo "Build it first, then re-run this script:" >&2
  echo "  cd template/admin && npm install && npm run build" >&2
  echo "Or build via Docker (recommended on servers):" >&2
  echo "  cd template/admin && docker run --rm -t -v \"\$PWD\":/app -w /app node:16-bullseye bash -lc \"npm ci --legacy-peer-deps && npm run build\"" >&2
  exit 1
fi

if [[ ! -f "$src_dir/index.html" ]]; then
  echo "Missing required output: $src_dir/index.html" >&2
  exit 1
fi

mkdir -p "$dest_dir"

if [[ "$backup_enabled" != "0" ]]; then
  timestamp="$(date +"%Y%m%d_%H%M%S")"
  backup_dir="$backup_root/admin/$timestamp"
  mkdir -p "$backup_dir"

  if [[ -d "$dest_dir" ]] && [[ -n "$(ls -A "$dest_dir" 2>/dev/null || true)" ]]; then
    if command -v rsync >/dev/null 2>&1; then
      rsync -a "$dest_dir/" "$backup_dir/"
    else
      cp -R "$dest_dir/." "$backup_dir/"
    fi
    echo "Backup created: $backup_dir"
  else
    rmdir "$backup_dir" 2>/dev/null || true
  fi
fi

if command -v rsync >/dev/null 2>&1; then
  rsync -a --delete "$src_dir/" "$dest_dir/"
else
  shopt -s dotglob nullglob
  rm -rf "$dest_dir"/*
  cp -R "$src_dir/." "$dest_dir/"
fi

echo "Published admin (Vue) to: $dest_dir"

