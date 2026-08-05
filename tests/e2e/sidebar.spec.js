import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

test('sidebar collapses and expands on desktop', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');

    const sidebar = page.locator('[data-slot="sidebar"]').first();
    const dashboardLink = page.getByRole('link', { name: 'Dashboard' });

    await expect(sidebar).toHaveAttribute('data-state', 'expanded');
    const expandedBox = await dashboardLink.boundingBox();
    expect(expandedBox.x).toBeGreaterThanOrEqual(0);

    await page.keyboard.press('Control+b');
    await expect(sidebar).toHaveAttribute('data-state', 'collapsed');
    await expect(sidebar).toHaveAttribute('data-collapsible', 'offcanvas');
    await page.waitForTimeout(300);
    const collapsedBox = await dashboardLink.boundingBox();
    expect(collapsedBox.x).toBeLessThan(0);

    await page.keyboard.press('Control+b');
    await expect(sidebar).toHaveAttribute('data-state', 'expanded');
    await page.waitForTimeout(300);
    const reExpandedBox = await dashboardLink.boundingBox();
    expect(reExpandedBox.x).toBeGreaterThanOrEqual(0);
});

test('sidebar presents as a mobile drawer', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/dashboard?month=2026-08');

    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeHidden();

    await page.getByRole('button', { name: 'Toggle Sidebar' }).click();

    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Comparaison' })).toBeVisible();
});
