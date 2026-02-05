param([string]$Url="mysql://user:pass@127.0.0.1:3306/infra")
Write-Host "Apply infra schema via mysql client"
# Expecting mysql client available in PATH
$match = [regex]::Match($Url, "mysql://(?<user>[^:]+):(?<pass>[^@]+)@(?<host>[^:/]+):(?<port>\d+)/(?<db>[^?]+)")
if (-not $match.Success) { Write-Error "Bad INFRA_URL"; exit 2 }
$user=$match.Groups['user'].Value; $pass=$match.Groups['pass'].Value; $host=$match.Groups['host'].Value; $port=$match.Groups['port'].Value; $db=$match.Groups['db'].Value
mysql --user=$user --password=$pass --host=$host --port=$port $db < ops/sql/mysql/infra_payment_projection.sql
