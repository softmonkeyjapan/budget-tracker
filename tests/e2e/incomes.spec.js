import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

const mockupPath = path.resolve(
    path.dirname(fileURLToPath(import.meta.url)),
    '../../docs/design/build/index.html',
);

// See tests/e2e/categories.spec.js for why the live page and the static
// mockup are captured as independent baselines rather than pixel-diffed.
test('incomes create page matches baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/incomes/create?month=2026-08');
    await expect(page).toHaveScreenshot('incomes-create.png', { fullPage: true });
});

test('income mockup reference screenshot', async ({ page }) => {
    await page.goto(`file://${mockupPath}#income`);
    await expect(page).toHaveScreenshot('income-mockup-reference.png', { fullPage: true });
});

test('incomes index page matches baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/incomes?month=2026-08');
    await expect(page).toHaveScreenshot('incomes-index.png', { fullPage: true });
});
