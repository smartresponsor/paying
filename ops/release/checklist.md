# GA checklist (Payment)

- [ ] composer.lock committed
- [ ] composer install proven from committed lock
- [ ] doctrine migrations proven on data/infra stores
- [ ] doctrine fixtures load proven with payment fixture group
- [ ] DB migrations applied (data/infra)
- [ ] /metrics exported in Prometheus
- [ ] Alerts loaded
- [ ] SLO gate passes (payment:gate:slo)
- [ ] Webhook secrets configured
- [ ] Rate limiter tuned
- [ ] OIDC JWKS cache URL set
- [ ] install preflight scripts pass
