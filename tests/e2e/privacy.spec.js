import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

const PRIVACY_TOGGLE = 'button[aria-label="Masquer les montants"]';

test('clicking the eye icon blurs amounts', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');

    const amount = page.locator('[data-amount]').first();
    await expect(amount).not.toHaveClass(/blur-sm/);

    await page.click(PRIVACY_TOGGLE);

    await expect(amount).toHaveClass(/blur-sm/);
});

test('privacy choice persists across navigation and reload', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');
    await page.click(PRIVACY_TOGGLE);

    await expect(page.locator('html')).toHaveAttribute('data-privacy', 'hidden');

    await page.goto('/categories');
    await expect(page.locator('html')).toHaveAttribute('data-privacy', 'hidden');

    await page.reload();
    await expect(page.locator('html')).toHaveAttribute('data-privacy', 'hidden');
});

test('toggle reveals amounts again', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');
    await page.click(PRIVACY_TOGGLE);

    const amount = page.locator('[data-amount]').first();
    await expect(amount).toHaveClass(/blur-sm/);

    await page.click('button[aria-label="Afficher les montants"]');
    await expect(amount).not.toHaveClass(/blur-sm/);
});
