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
test('dashboard page matches baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');
    await expect(page).toHaveScreenshot('dashboard.png', { fullPage: true });
});

test('dashboard mockup reference screenshot', async ({ page }) => {
    await page.goto(`file://${mockupPath}#dashboard`);
    await expect(page).toHaveScreenshot('dashboard-mockup-reference.png', { fullPage: true });
});
