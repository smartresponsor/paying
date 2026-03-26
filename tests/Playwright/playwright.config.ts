// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './specs',
  timeout: 30_000,
  expect: {
    timeout: 5_000,
  },
  fullyParallel: false,
  retries: 0,
  reporter: [['list']],
  use: {
    baseURL: 'http://127.0.0.1:9080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'OIDC_DISABLED=1 APP_ENV=test php -S 127.0.0.1:9080 -t public',
    url: 'http://127.0.0.1:9080/status',
    reuseExistingServer: true,
    timeout: 120_000,
  },
});
