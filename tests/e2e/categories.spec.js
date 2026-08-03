import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect, test } from '@playwright/test';
import { login } from './helpers.js';

const mockupPath = path.resolve(
    path.dirname(fileURLToPath(import.meta.url)),
    '../../docs/design/build/index.html',
);

// The static mockup and the compiled Vue page never render byte-identical
// pixels (different font loading, build pipeline, no shared DOM) so an
// automated pixel-diff between the two would be too brittle to gate on.
// Both are captured as independent baselines instead, reviewed side by side
// against docs/design/build/index.html#categories on every Story 2+ change.
test('categories page matches baseline', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/categories');
    await expect(page).toHaveScreenshot('categories.png', { fullPage: true });
});

test('categories mockup reference screenshot', async ({ page }) => {
    await page.goto(`file://${mockupPath}#categories`);
    await expect(page).toHaveScreenshot('categories-mockup-reference.png', { fullPage: true });
});
