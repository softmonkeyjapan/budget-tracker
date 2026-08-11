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

// The seeded category tree (see app/Console/Commands/SeedE2eUsers.php) gives
// "Alimentaire" three children: Alimentation générale, Boucherie, Restaurants.
test('checking a root category checks all its children, and unchecking one leaves it partially checked', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/expenses?month=2026-08');

    await page.getByRole('button', { name: 'Toutes les catégories' }).click();

    const root = page.getByRole('checkbox', { name: 'Alimentaire' });
    const child = page.getByRole('checkbox', { name: 'Alimentation générale' });
    const otherChild = page.getByRole('checkbox', { name: 'Boucherie' });

    await root.click();

    await expect(child).toHaveAttribute('aria-checked', 'true');
    await expect(otherChild).toHaveAttribute('aria-checked', 'true');
    await expect(root).toHaveAttribute('aria-checked', 'true');

    await otherChild.click();

    await expect(otherChild).toHaveAttribute('aria-checked', 'false');
    await expect(child).toHaveAttribute('aria-checked', 'true');
    await expect(root).toHaveAttribute('aria-checked', 'mixed');
});

test('selecting a main category filters the expense list to its children only', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/expenses?month=2026-08');

    await page.getByRole('button', { name: 'Toutes les catégories' }).click();
    await page.getByRole('checkbox', { name: 'Alimentaire' }).click();
    await page.keyboard.press('Escape');

    await expect(page.getByText('Supermarché')).toBeVisible();
    await expect(page.getByText('Appartement')).toHaveCount(0);
    await expect(page.getByText('Station-service')).toHaveCount(0);
});

test('the "Générale" chart tab is hidden for a single main category and shown by default for two or more', async ({ page }) => {
    await login(page, 'e2e-verified@example.com', 'password');
    await page.goto('/expenses?month=2026-08');

    await page.getByRole('button', { name: 'Toutes les catégories' }).click();
    await page.getByRole('checkbox', { name: 'Alimentaire' }).click();
    await page.keyboard.press('Escape');

    await expect(page.getByRole('button', { name: 'Générale' })).toHaveCount(0);
    await expect(page.getByText('Regroupées par sous-catégorie')).toBeVisible();

    await page.getByRole('button', { name: 'Alimentaire' }).click();
    await page.getByRole('checkbox', { name: 'Transport' }).click();
    await page.keyboard.press('Escape');

    await expect(page.getByRole('button', { name: 'Générale' })).toBeVisible();
    await expect(page.getByText('Regroupées par catégorie principale')).toBeVisible();
});
