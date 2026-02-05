$Base = 'http://localhost:8000'
$resp = Invoke-RestMethod -Method Post "$Base/payment/start" -Headers @{'Content-Type'='application/json'; 'Idempotency-Key'='e2e-1'} -Body '{"amount":"1.23","currency":"USD","provider":"internal"}'
$resp | ConvertTo-Json
