#!/usr/bin/env bash
URL="${INFRA_URL:-mysql://user:pass@127.0.0.1:3306/infra}"
echo "Apply infra schema via mysql client"
if [[ "$URL" =~ mysql://([^:]+):([^@]+)@([^:/]+):([0-9]+)/([^?]+) ]]; then
  user="${BASH_REMATCH[1]}"; pass="${BASH_REMATCH[2]}"; host="${BASH_REMATCH[3]}"; port="${BASH_REMATCH[4]}"; db="${BASH_REMATCH[5]}"
  mysql --user="$user" --password="$pass" --host="$host" --port="$port" "$db" < ops/sql/mysql/infra_payment_projection.sql
else
  echo "Bad INFRA_URL"; exit 2
fi
