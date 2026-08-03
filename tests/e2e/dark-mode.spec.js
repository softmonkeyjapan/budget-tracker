import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

const THEME_TOGGLE = 'button[aria-label="Activer le mode sombre"]';

test('dashboard matches dark mode baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');
    await page.click(THEME_TOGGLE);
    await expect(page).toHaveScreenshot('dashboard-dark.png', { fullPage: true });
});

test('theme choice persists across navigation and reload', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');
    await page.click(THEME_TOGGLE);

    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

    await page.goto('/categories');
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

    await page.reload();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
});

test('toggle switches back to light mode', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');
    await page.click(THEME_TOGGLE);
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');

    await page.click('button[aria-label="Activer le mode clair"]');
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
});
