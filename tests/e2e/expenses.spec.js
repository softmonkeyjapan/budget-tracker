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
test('expenses create page matches baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/expenses/create?month=2026-08');
    await expect(page).toHaveScreenshot('expenses-create.png', { fullPage: true });
});

test('expense mockup reference screenshot', async ({ page }) => {
    await page.goto(`file://${mockupPath}#expense`);
    await expect(page).toHaveScreenshot('expense-mockup-reference.png', { fullPage: true });
});

test('expenses index page matches baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/expenses?month=2026-08');
    await expect(page).toHaveScreenshot('expenses-index.png', { fullPage: true });
});
