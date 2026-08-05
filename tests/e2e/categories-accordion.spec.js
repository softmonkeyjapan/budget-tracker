import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

test('accordion is exclusive and toggles', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/categories');

    const roots = page.locator('.rounded-card.bg-surface.p-5.shadow-soft');
    const firstRoot = roots.nth(0);
    const secondRoot = roots.nth(1);

    await expect(firstRoot.locator('.ms-5.mt-4')).toHaveCount(0);
    await expect(secondRoot.locator('.ms-5.mt-4')).toHaveCount(0);

    await firstRoot.getByRole('button').first().click();
    await expect(firstRoot.locator('.ms-5.mt-4')).toBeVisible();
    await expect(secondRoot.locator('.ms-5.mt-4')).toHaveCount(0);

    await secondRoot.getByRole('button').first().click();
    await expect(firstRoot.locator('.ms-5.mt-4')).toHaveCount(0);
    await expect(secondRoot.locator('.ms-5.mt-4')).toBeVisible();

    await secondRoot.getByRole('button').first().click();
    await expect(secondRoot.locator('.ms-5.mt-4')).toHaveCount(0);
});
