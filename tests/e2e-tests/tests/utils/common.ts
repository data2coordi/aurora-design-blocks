// utils/customizer.ts
import { expect, type Page } from '@playwright/test';

// -------------------- 計測系 --------------------
const stepTimers = new Map<string, number>();



// -------------------- デバッグ系 --------------------

/**
 * 画面上にコード内容を可視表示する（動画出力やデバッグ用）
 * @param page PlaywrightのPageオブジェクト
 * @param code 表示したいコード文字列
 */
export async function showCodeOverlay(page: Page, code: string) {
    await page.evaluate((code) => {
        const existing = document.getElementById('visible-script-code');
        if (existing) existing.remove();

        const el = document.createElement('pre');
        el.textContent = code;
        el.style.position = 'fixed';
        el.style.top = '0';
        el.style.left = '0';
        el.style.padding = '10px';
        el.style.background = 'white';
        el.style.color = 'black';
        el.style.fontSize = '14px';
        el.style.zIndex = '99999';
        el.id = 'visible-script-code';
        document.body.appendChild(el);
    }, code);

    // デバッグ確認用の一時待機
    await page.waitForTimeout(3000);
}




export function timeStart(stepName: string) {
    stepTimers.set(stepName, Date.now());
}

export function logStepTime(stepName: string) {
    const startTime = stepTimers.get(stepName);
    if (startTime) {
        const duration = Date.now() - startTime;
        console.log(`[Timer] Step "${stepName}" took ${duration}ms`);
    }
}

// -------------------- カスタマイザー操作系 --------------------
export async function openCustomizer(page: Page) {
    await page.goto(`/wp-admin/customize.php?url=${encodeURIComponent('/')}`, {
        waitUntil: 'networkidle',
    });
    await expect(page.locator('.wp-full-overlay-main')).toBeVisible();
}

export async function openHeaderSetting(page: Page, setting: string) {
    await page.getByRole('button', { name: 'ヘッダー設定' }).click();
    await page.getByRole('button', { name: '1.「スライダー」タイプまたは「静止画像」タイプを選択' }).click();
    const effectSelect = page.getByRole('combobox', { name: '「スライダー」タイプまたは「静止画像」タイプを選択' });
    await effectSelect.selectOption({ label: setting });
}

export async function selSliderEffect(page: Page, effect: string = 'フェード', interval = '5') {
    await page.getByRole('button', { name: 'ヘッダー設定' }).click();
    await page.getByRole('button', { name: '2.スライダー設定' }).click();
    const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
    await effectSelect.selectOption({ label: effect });
    const intervalInput = page.getByLabel('変更時間間隔（秒）');
    await intervalInput.fill(interval);
}

export async function saveCustomizer(page: Page) {
    const saveBtn = page.locator('#save');
    if (!(await saveBtn.isEnabled())) {
        return;
    }
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
}

export async function setSiteType(page: Page, siteType: string = 'エレガント') {
    await page.getByRole('button', { name: 'サイト設定' }).click();

    await page.getByRole('button', { name: 'サイトタイプ設定' }).click();
    const checkbox = page.getByLabel(siteType);
    if (!(await checkbox.isChecked())) {
        await checkbox.check();
    }
    await expect(checkbox).toBeChecked();

}

export async function ensureCustomizerRoot(page: Page) {
    await page.evaluate(() => {
        if (window.wp && window.wp.customize) {
            try {
                window.wp.customize.panel.each(panel => {
                    if (typeof panel.collapse === 'function') panel.collapse();
                });
                window.wp.customize.section.each(section => {
                    if (typeof section.collapse === 'function') section.collapse();
                });
            } catch (e) {
                // fallback
            }
        }
    });
    await page.waitForTimeout(200);
}

// -------------------- 管理画面操作系 --------------------
/**
 * 指定したテーマに切り替える
 * @param page PlaywrightのPageオブジェクト
 * @param themeSlug テーマのスラッグ名
 */
export async function activateTheme(page: Page, themeSlug: string) {
    await page.goto(`/wp-admin/themes.php`, { waitUntil: 'networkidle' });

    const themeSelector = `.theme[data-slug="${themeSlug}"]`;
    const activateButton = page.locator(`${themeSelector} .theme-actions .activate`);
    const isActive = await page.locator(`${themeSelector}.active`).count();

    if (isActive === 0) {
        await activateButton.click();
        await page.waitForSelector(`${themeSelector}.active`, { timeout: 5000 });
    }
}


