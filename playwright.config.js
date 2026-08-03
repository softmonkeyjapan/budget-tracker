import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    reporter: 'list',
    globalSetup: './tests/e2e/global-setup.js',
    use: {
        baseURL: 'http://127.0.0.1:8010',
        trace: 'retain-on-failure',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
    webServer: {
        command: 'php artisan serve --port=8010',
        url: 'http://127.0.0.1:8010',
        reuseExistingServer: !process.env.CI,
        env: { APP_ENV: 'e2e' },
        timeout: 30_000,
    },
});
