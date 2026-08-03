import { expect, test } from '@playwright/test';

test('login page matches baseline', async ({ page }) => {
    await page.goto('/login');
    await expect(page).toHaveScreenshot('login.png', { fullPage: true });
});

test('register page matches baseline', async ({ page }) => {
    await page.goto('/register');
    await expect(page).toHaveScreenshot('register.png', { fullPage: true });
});

test('forgot password page matches baseline', async ({ page }) => {
    await page.goto('/forgot-password');
    await expect(page).toHaveScreenshot('forgot-password.png', { fullPage: true });
});

test('reset password page matches baseline', async ({ page }) => {
    await page.goto('/reset-password/e2e-token?email=e2e-verified@example.com');
    await expect(page).toHaveScreenshot('reset-password.png', { fullPage: true });
});
