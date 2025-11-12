import { test, expect, type Page } from '@playwright/test';
import { openCustomizer, saveCustomizer, ensureCustomizerRoot, activateTheme, showCodeOverlay } from '../utils/common';

// テストデータを配列でまとめる
const CUSTOMIZER_INPUTS = [
    {
        name: 'GA',
        buttonName: 'Google Analytics 設定',
        label: 'Google Analytics トラッキングコード',
        code: '<script>//UA-12345678-1</script>',
        outputTarget: 'head',
    },
    {
        name: 'GTM-head',
        buttonName: 'Google Tag Manager 設定',
        label: 'headタグに出力するコード',
        code: '<script>//GTM-head-12345678-1</script>',
        outputTarget: 'head',
    },
    {
        name: 'GTM-body',
        buttonName: 'Google Tag Manager 設定',
        label: 'bodyタグ開始直後に出力するコード',
        code: '<script>//GTM-body-12345678-1</script>',
        outputTarget: 'body',
    },
    {
        name: 'adSense',
        buttonName: 'Googleアドセンス自動広告',
        label: 'アドセンス自動広告コード',
        code: '<script>//adSense-head-12345678-1</script>',
        outputTarget: 'head',
    },
];

test.describe('カスタマイザー全パターンまとめテスト', () => {
    test('E2E: 4パターン入力・保存・フロント確認・復元確認', async ({ page }) => {
        // ---------------------------
        // 1. カスタマイザーを1回だけ開き、4パターン入力
        // ---------------------------
        await openCustomizer(page);
        for (const input of CUSTOMIZER_INPUTS) {
            await page.getByRole('button', { name: input.buttonName }).click();

            const field = page.getByLabel(input.label);
            await field.fill('');
            await field.fill(input.code);
            await ensureCustomizerRoot(page);
        }

        // 保存ボタンをクリック
        await saveCustomizer(page);

        // ---------------------------
        // 2. 保存後、カスタマイザー内で値が復元されるか確認
        // ---------------------------
        for (const input of CUSTOMIZER_INPUTS) {


            await page.getByRole('button', { name: input.buttonName }).click();
            const field = page.getByLabel(input.label);
            await expect(field).toHaveValue(input.code);
            await ensureCustomizerRoot(page);
        }

        // ---------------------------
        // 3. フロントで出力されているか確認（E2E-03相当）
        // ---------------------------
        await page.goto('/', { waitUntil: 'networkidle' });
        for (const input of CUSTOMIZER_INPUTS) {
            const content = await page.locator(input.outputTarget).innerHTML();
            expect(content).toContain(input.code);
            // デバッグ用表示（必要な場合のみ）
            await showCodeOverlay(page, input.code);
        }

        // ---------------------------
        // 4. Twenty Twentyでフロント出力を確認（E2E-04相当）
        // ---------------------------
        await activateTheme(page, 'twentytwenty');
        await page.goto('/', { waitUntil: 'networkidle' });
        for (const input of CUSTOMIZER_INPUTS) {
            const content = await page.locator(input.outputTarget).innerHTML();
            expect(content).toContain(input.code);
        }

        // ---------------------------
        // 5. Twenty Twentyで保存した値が復元されるか確認（E2E-05相当）
        // ---------------------------
        await openCustomizer(page);
        for (const input of CUSTOMIZER_INPUTS) {
            await page.getByRole('button', { name: input.buttonName }).click();
            const field = page.getByLabel(input.label);
            await expect(field).toHaveValue(input.code);
            await ensureCustomizerRoot(page);
        }

        // 元テーマに戻す
        await activateTheme(page, 'integlight');
    });
});
