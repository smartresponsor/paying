// updated playwright test
import {test, expect} from '@playwright/test';

test('finalize uses providerTransactionId', async ({page}) => {
  await page.goto('/payment/console');

  await page.locator('[name="payment_console_finalize[paymentId]"]').fill('01HK153X000000000000000099');
  await page.locator('[name="payment_console_finalize[provider]"]').selectOption('internal');
  await page.locator('[name="payment_console_finalize[providerRef]"]').fill('ref');
  await page.locator('[name="payment_console_finalize[providerTransactionId]"]').fill('txn');
  await page.locator('[name="payment_console_finalize[status]"]').selectOption('completed');

  await page.getByRole('button', {name: 'Finalize payment'}).click();

  await expect(page).toHaveURL(/payment\/console/);
});
