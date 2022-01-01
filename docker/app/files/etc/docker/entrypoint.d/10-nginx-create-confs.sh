#!/bin/sh

set -eu

ME=$(basename "$0")

# internal と tool は prefix に '-' が必要なので '-' 付き版を付与する
export DASHED_DOMAIN_PREFIX=${DOMAIN_PREFIX:+${DOMAIN_PREFIX}-}

output_dir="/etc/nginx/http.d"
suffix=".template"

defined_envs=$(printf "\${%s} " $(env | sort | cut -d= -f1))

find "$output_dir" -follow -type f -name "*$suffix" -print | while read -r template; do
  relative_path="${template#$output_dir/}"
  output_path="$output_dir/${relative_path%$suffix}"
  echo >&1 "${ME}: Running envsubst on $template to $output_path"
  envsubst "$defined_envs" < "$template" > "$output_path"
done
