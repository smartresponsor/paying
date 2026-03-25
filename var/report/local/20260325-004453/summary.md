# Payment local pipeline report

- Timestamp: 20260325-004453
- Report root: `D:\PhpstormProjects\www\Paying\var\report\local\20260325-004453`
- Result: PASSED_WITH_ISSUES
- Duration ms: 57290
- Include smokes: True
- Include reports: True
- Fail on errors: False

| Step | Status | Exit | Duration ms | Log |
|---|---:|---:|---:|---|
| install-preflight | passed | 0 | 1666 | `logs/install-preflight.log` |
| lint | passed | 0 | 15592 | `logs/lint.log` |
| lint-yaml | passed | 0 | 1818 | `logs/lint-yaml.log` |
| lint-container | passed | 0 | 5368 | `logs/lint-container.log` |
| cs-check | failed | 1 | 4579 | `logs/cs-check.log` |
| stan | failed | 1 | 3460 | `logs/stan.log` |
| docs-phpdoc-check | passed | 0 | 1574 | `logs/docs-phpdoc-check.log` |
| test-bootstrap-reset | passed | 0 | 1800 | `logs/test-bootstrap-reset.log` |
| test | passed | 0 | 10987 | `logs/test.log` |
| smoke-runtime | passed | 0 | 1588 | `logs/smoke-runtime.log` |
| smoke-fixtures | passed | 0 | 1604 | `logs/smoke-fixtures.log` |
| smoke-container | failed | 1 | 1848 | `logs/smoke-container.log` |
| smoke-doctrine | passed | 0 | 1758 | `logs/smoke-doctrine.log` |
| report-route-inventory | passed | 0 | 1707 | `logs/report-route-inventory.log` |
| report-runtime-proof | passed | 0 | 1820 | `logs/report-runtime-proof.log` |
