// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import {defineConfig, devices} from '@playwright/test';

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
        baseURL: 'http://127.0.0.1:18006',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: {...devices['Desktop Chrome']},
        },
    ],
    webServer: {
        command: 'php -S 127.0.0.1:18006 -t ../../public ../../tools/runtime/payment_playwright_router.php',
        url: 'http://127.0.0.1:18006/payment/console',
        reuseExistingServer: true,
        timeout: 120_000,
    },
});
