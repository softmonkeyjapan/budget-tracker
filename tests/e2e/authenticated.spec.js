import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

const VERIFIED_USER = { email: 'e2e-verified@example.com', password: 'password' };
const UNVERIFIED_USER = { email: 'e2e-unverified@example.com', password: 'password' };

// The Dashboard page moved from a Story 1 placeholder to a full feature in
// Story 5 — see tests/e2e/dashboard.spec.js for its (pinned-month) baseline.

test('profile edit page matches baseline', async ({ page }) => {
    await login(page, VERIFIED_USER.email, VERIFIED_USER.password);
    await page.goto('/profile');
    await expect(page).toHaveScreenshot('profile-edit.png', { fullPage: true });
});

test('confirm password page matches baseline', async ({ page }) => {
    await login(page, VERIFIED_USER.email, VERIFIED_USER.password);
    await page.goto('/confirm-password');
    await expect(page).toHaveScreenshot('confirm-password.png', { fullPage: true });
});

test('verify email page matches baseline', async ({ page }) => {
    await login(page, UNVERIFIED_USER.email, UNVERIFIED_USER.password);
    await page.goto('/verify-email');
    await expect(page).toHaveScreenshot('verify-email.png', { fullPage: true });
});
