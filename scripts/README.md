# 前端构建发布

这些脚本用于把构建后的前端产物复制/同步到 PHP 项目的 `public` 目录结构中。

## uni-app（H5）

1. 在 HBuilderX 中构建（项目：`template/uni-app`），生成：
   - `template/uni-app/unpackage/dist/build/web/`
2. 发布：
   - `bash scripts/publish-uni-h5.sh`

该脚本会更新 `crmeb/public/index.html`，并替换 `crmeb/public/{assets,pages,static}`。

发布前会在 `crmeb/backup/frontend/uni-h5/<timestamp>/` 下创建一份备份。
如需在某次执行中禁用备份：`CRMEB_PUBLISH_BACKUP=0 bash scripts/publish-uni-h5.sh`。

## PC（Nuxt）

1. 构建：
   - `cd template/pc && npm i && npm run generate`
2. 发布：
   - `bash scripts/publish-pc.sh`

该脚本会把 `template/pc/dist/` 同步到 `crmeb/public/home/`。
