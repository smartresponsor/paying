# Payment local pipeline report

- Timestamp: 20260326-213741
- Report root: `D:\PhpstormProjects\www\Paying\var\report\local\20260326-213741`
- Result: PASSED_WITH_ISSUES
- Duration ms: 121382
- Include smokes: True
- Include reports: True
- Include security: True
- Fail on errors: False

| Step | Status | Exit | Duration ms | Log |
|---|---:|---:|---:|---|
| install-preflight | passed | 0 | 1569 | `logs/install-preflight.log` |
| lint | passed | 0 | 18812 | `logs/lint.log` |
| lint-yaml | passed | 0 | 1904 | `logs/lint-yaml.log` |
| lint-container | passed | 0 | 5740 | `logs/lint-container.log` |
| cs-check | passed | 0 | 2006 | `logs/cs-check.log` |
| stan | passed | 0 | 14406 | `logs/stan.log` |
| docs-phpdoc-check | passed | 0 | 1868 | `logs/docs-phpdoc-check.log` |
| test-bootstrap-reset | passed | 0 | 2499 | `logs/test-bootstrap-reset.log` |
| test | passed | 0 | 24688 | `logs/test.log` |
| test-ui-playwright | failed | 1 | 4663 | `logs/test-ui-playwright.log` |
| smoke-runtime | passed | 0 | 3070 | `logs/smoke-runtime.log` |
| smoke-fixtures | passed | 0 | 2776 | `logs/smoke-fixtures.log` |
| smoke-container | passed | 0 | 11305 | `logs/smoke-container.log` |
| smoke-doctrine | passed | 0 | 2919 | `logs/smoke-doctrine.log` |
| report-route-inventory | passed | 0 | 2886 | `logs/report-route-inventory.log` |
| report-runtime-proof | passed | 0 | 3094 | `logs/report-runtime-proof.log` |
| security-composer-audit | failed | 2 | 5219 | `logs/security-composer-audit.log` |
| security-importmap-audit | failed | 1 | 3700 | `logs/security-importmap-audit.log` |
| security-gitleaks | failed | 1 | 2744 | `logs/security-gitleaks.log` |
| security-semgrep-ce | failed | 1 | 2919 | `logs/security-semgrep-ce.log` |
| test-security | passed | 0 | 2166 | `logs/test-security.log` |
