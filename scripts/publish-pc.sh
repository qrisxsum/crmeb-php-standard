#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
src_dir="$repo_root/template/pc/dist"
dest_dir="$repo_root/crmeb/public/home"
backup_root="$repo_root/crmeb/backup/frontend"
backup_enabled="${CRMEB_PUBLISH_BACKUP:-1}"

if [[ ! -d "$src_dir" ]]; then
  echo "PC build output not found: $src_dir" >&2
  echo "Run 'npm run generate' in template/pc first, then re-run this script." >&2
  exit 1
fi

mkdir -p "$dest_dir"

if [[ "$backup_enabled" != "0" ]]; then
  timestamp="$(date +"%Y%m%d_%H%M%S")"
  backup_dir="$backup_root/pc/$timestamp"
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

# nuxt generate 在 build.publicPath="/home/" 时，默认会把 JS/CSS 产物输出到 dist/home/。
# 但 nginx 常见配置是 root 指向 public，PC 通过 /home/ 访问：此时 /home/*.js 应该在 public/home/*.js。
#
# 因此发布时需要把 dist/home/ “扁平化”到 public/home/ 根目录（而不是产生 public/home/home/）。
assets_dir="$src_dir/home"
flatten_assets=0
if [[ -d "$assets_dir" ]] && [[ ! -f "$assets_dir/index.html" ]]; then
  if compgen -G "$assets_dir/*.js" >/dev/null 2>&1; then
    flatten_assets=1
  fi
fi

# 清空目标目录（避免 rsync --exclude 导致的残留）
rm -rf "$dest_dir"
mkdir -p "$dest_dir"

if command -v rsync >/dev/null 2>&1; then
  if [[ "$flatten_assets" == "1" ]]; then
    rsync -a --exclude '/home/' "$src_dir/" "$dest_dir/"
    rsync -a "$assets_dir/" "$dest_dir/"
  else
    rsync -a "$src_dir/" "$dest_dir/"
  fi
else
  shopt -s dotglob nullglob
  if [[ "$flatten_assets" == "1" ]]; then
    for item in "$src_dir"/*; do
      [[ "$(basename "$item")" == "home" ]] && continue
      cp -R "$item" "$dest_dir/"
    done
    cp -R "$assets_dir/." "$dest_dir/"
  else
    cp -R "$src_dir/." "$dest_dir/"
  fi
fi

echo "Published PC (Nuxt) to: $dest_dir"
