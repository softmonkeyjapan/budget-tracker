import { execSync } from 'node:child_process';
import { copyFileSync, existsSync } from 'node:fs';

const env = { ...process.env, APP_ENV: 'e2e' };

export default async function globalSetup() {
    if (!existsSync('.env.e2e')) {
        copyFileSync('.env.e2e.example', '.env.e2e');
        execSync('php artisan key:generate --force', { env, stdio: 'inherit' });
    }

    if (!existsSync('database/e2e.sqlite')) {
        execSync('touch database/e2e.sqlite');
    }

    execSync('npm run build', { stdio: 'inherit' });
    execSync('php artisan migrate:fresh --force', { env, stdio: 'inherit' });
    execSync('php artisan e2e:seed', { env, stdio: 'inherit' });
}
