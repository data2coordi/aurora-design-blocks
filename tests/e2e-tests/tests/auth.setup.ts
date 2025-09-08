// tests/auth.setup.ts
import { test as setup } from '@playwright/test';
import * as path from 'path';

const authFile = path.join(__dirname, '..', 'playwright/.auth/user.json');

setup('authenticate', async ({ page }) => {
    await page.goto('https://wpdev.auroralab-design.com/wp-login.php');
    await page.getByLabel('ユーザー名またはメールアドレス').fill(process.env.WP_ADMIN_USER!);
    await page.getByLabel('パスワード', { exact: true }).fill(process.env.WP_ADMIN_PASSWORD!);
    await page.getByRole('button', { name: 'ログイン' }).click();
    // いったんロードが落ち着くまで待機
    await page.waitForLoadState('networkidle');

    // confirm_admin_email ページに来たら対応
    if (page.url().includes('action=confirm_admin_email')) {
        // 「正しいメールアドレスです」ボタンを押す
        const confirmBtn = page.getByRole('button', { name: '正しいメールアドレスです' });
        await confirmBtn.waitFor({ state: 'visible' });
        await confirmBtn.click();

        // フォーム送信後、管理画面に遷移するはず
    }
    await page.waitForURL('**/wp-admin/');
    await page.context().storageState({ path: authFile });
});