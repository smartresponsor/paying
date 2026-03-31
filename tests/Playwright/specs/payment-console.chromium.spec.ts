// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
import {test, expect} from '@playwright/test';

test.describe('Payment console Chromium coverage', () => {
    test('shows operator actions and error path for missing payment finalization', async ({page}) => {
        await page.goto('/payment/console');

        await expect(page.getByRole('heading', {name: 'Payment Console'})).toBeVisible();
        await expect(page.getByRole('heading', {name: 'Finalize payment'})).toBeVisible();

        await page.locator('[name="payment_console_finalize[paymentId]"]').fill('01HK153X000000000000000099');
        await page.locator('[name="payment_console_finalize[provider]"]').selectOption('internal');
        await page.locator('[name="payment_console_finalize[providerRef]"]').fill('missing-target');
        await page.locator('[name="payment_console_finalize[gatewayTransactionId]"]').fill('txn-missing-target');
        await page.locator('[name="payment_console_finalize[status]"]').selectOption('completed');

        await page.getByRole('button', {name: 'Finalize payment'}).click();

        await expect(page).toHaveURL(/\/payment\/console/);
        await expect(page.locator('.alert-danger')).toContainText('was not found');
    });
});
