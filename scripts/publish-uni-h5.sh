#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
src_dir="$repo_root/template/uni-app/unpackage/dist/build/web"
dest_dir="$repo_root/crmeb/public"
backup_root="$repo_root/crmeb/backup/frontend"
backup_enabled="${CRMEB_PUBLISH_BACKUP:-1}"

if [[ ! -d "$src_dir" ]]; then
  echo "H5 build output not found: $src_dir" >&2
  echo "Build uni-app (H5) in HBuilderX first, then re-run this script." >&2
  exit 1
fi

for required in index.html assets pages static; do
  if [[ ! -e "$src_dir/$required" ]]; then
    echo "Missing required output: $src_dir/$required" >&2
    exit 1
  fi
done

if [[ "$backup_enabled" != "0" ]]; then
  timestamp="$(date +"%Y%m%d_%H%M%S")"
  backup_dir="$backup_root/uni-h5/$timestamp"
  mkdir -p "$backup_dir"

  has_existing=0
  for item in index.html assets pages static; do
    if [[ -e "$dest_dir/$item" ]]; then
      has_existing=1
      break
    fi
  done

  if [[ "$has_existing" == "1" ]]; then
    if command -v rsync >/dev/null 2>&1; then
      for item in index.html assets pages static; do
        [[ -e "$dest_dir/$item" ]] || continue
        rsync -a "$dest_dir/$item" "$backup_dir/"
      done
    else
      for item in index.html assets pages static; do
        [[ -e "$dest_dir/$item" ]] || continue
        cp -R "$dest_dir/$item" "$backup_dir/"
      done
    fi
    echo "Backup created: $backup_dir"
  else
    rmdir "$backup_dir" 2>/dev/null || true
  fi
fi

rm -rf \
  "$dest_dir/assets" \
  "$dest_dir/pages" \
  "$dest_dir/static"

cp -R "$src_dir/assets" "$dest_dir/"
cp -R "$src_dir/pages" "$dest_dir/"
cp -R "$src_dir/static" "$dest_dir/"
cp "$src_dir/index.html" "$dest_dir/index.html"

echo "Published uni-app (H5) to: $dest_dir"
