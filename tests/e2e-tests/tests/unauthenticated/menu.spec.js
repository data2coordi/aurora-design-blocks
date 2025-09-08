import { test, expect } from '@playwright/test';


/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースPC操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
test('PC-01: メインメニュー → サブメニュー → サブサブメニューの開閉と閉じる確認', async ({ page }) => {
  // 1. ページを開く
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // 2. 最初のメインメニューを取得してクリック
  const mainMenuItem = page.locator('.main-navigation .menu-item-has-children').first();
  const mainLink = mainMenuItem.locator('> a');
  const mainBox = await mainLink.boundingBox();
  if (!mainBox) throw new Error('メインメニューリンクのboundingBoxが取得できません');
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });

  // 3. サブメニューが開いていることを確認
  const subMenu = mainMenuItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();
  await expect(mainMenuItem).toHaveClass(/active/);

  await page.waitForTimeout(500);

  // 4. サブメニュー内のサブサブ開閉トリガーをクリック
  const subSubParentItem = subMenu.locator('li.menu-item-has-children').first();
  const subSubLink = subSubParentItem.locator('> a');
  const subSubBox = await subSubLink.boundingBox();
  if (!subSubBox) throw new Error('サブサブメニューリンクのboundingBoxが取得できません');
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });

  // 5. サブサブメニューが開いていることを確認
  const subSubMenuList = subSubParentItem.locator('> .sub-menu');
  await expect(subSubMenuList).toBeVisible();
  await expect(subSubParentItem).toHaveClass(/active/);

  await page.waitForTimeout(500);


  // 6. サブサブメニューを閉じる（再クリック）
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });
  await expect(subSubMenuList).toBeHidden();
  await expect(subSubParentItem).not.toHaveClass(/active/);

  // サブメニューはまだ開いているべき
  await expect(subMenu).toBeVisible();

  // 7. サブメニューを閉じる（再クリック）
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });
  await expect(subMenu).toBeHidden();
  await expect(mainMenuItem).not.toHaveClass(/active/);


});


/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースSP操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
test('モバイルでハンバーガーメニューの開閉ができる', async ({ page }) => {
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });

  // ページにアクセス
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // メニューコンテナ（開閉対象）とトグルボタン取得
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');

  // 初期状態は閉じている
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

  // クリックして開く
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');

  // 再度クリックして閉じる
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');
});




test('SP-01: メインメニュー → サブメニュー → サブサブメニューの開閉', async ({ page }) => {

  // 1. ビューポートをモバイルサイズに設定し、ページを開く
  await page.setViewportSize({ width: 375, height: 812 }); // iPhone X相当



  // ページにアクセス
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // メニューコンテナ（開閉対象）とトグルボタン取得
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');

  // 初期状態は閉じている
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

  // クリックして開く
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');







  // 2. 最初の「子を持つメニュー」(メインメニュー)を取得しクリック
  const mainMenuItem = page.locator('.main-navigation .menu-item-has-children').first();
  const mainLink = mainMenuItem.locator('> a');
  const mainBox = await mainLink.boundingBox();
  if (!mainBox) throw new Error('メインメニューリンクのboundingBoxが取得できません');
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });

  // 3. サブメニューが開き、親の <li> に .active が付いていることを確認
  const subMenu = mainMenuItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();
  await expect(mainMenuItem).toHaveClass(/active/);

  await page.waitForTimeout(1000);

  // 4. サブメニュー内の「子を持つメニュー」(サブサブ開閉トリガー)を取得しクリック
  const subSubParentItem = subMenu.locator('li.menu-item-has-children').first();
  const subSubLink = subSubParentItem.locator('> a');
  const subSubBox = await subSubLink.boundingBox();
  if (!subSubBox) throw new Error('サブサブメニューリンクのboundingBoxが取得できません');
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });

  // 5. サブサブメニューが開き、クリックした <li> に .active が付いていることを確認
  const subSubMenuList = subSubParentItem.locator('> .sub-menu');
  await expect(subSubMenuList).toBeVisible();
  await expect(subSubParentItem).toHaveClass(/active/);

  await page.waitForTimeout(500);

  // 6. サブサブメニューを閉じる（再クリック）
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });
  await expect(subSubMenuList).toBeHidden();
  await expect(subSubParentItem).not.toHaveClass(/active/);

  // サブメニューはまだ開いているべき
  await expect(subMenu).toBeVisible();

  // 7. サブメニューを閉じる（再クリック）
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });
  await expect(subMenu).toBeHidden();
  await expect(mainMenuItem).not.toHaveClass(/active/);


});


/****************************************************************************************:     */
/****************************************************************************************:     */
/*キーボードPC操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */

test('PC-02: Tabでメイン→サブ→サブサブ開いて、ESCで全閉じ確認', async ({ page }) => {
  // 1. ページを開く
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // 2. メインメニューをTabでフォーカス→開く
  const mainLink = page.locator('.main-navigation .menu-item-has-children > a').first();
  await mainLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const mainItem = mainLink.locator('..');
  await expect(mainItem).toHaveClass(/active/);


  // 3. サブメニューが可視化されていることを確認
  const subMenu = mainItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 4. サブサブを持つリンクをTabでフォーカス→開く
  const subSubLink = subMenu.locator('li.menu-item-has-children > a').first();
  //await subSubLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const subSubItem = subSubLink.locator('..');
  await expect(subSubItem).toHaveClass(/active/);

  // 5. サブサブメニューが可視化されていることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(500);


  // 6. ESCキーを一度押して全階層を閉じる
  await page.keyboard.press('Escape');

  // 7. どの階層にも .active がなく、サブ・サブサブともに隠れていること
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();
  await expect(subSubMenu).toBeHidden();

  await page.waitForTimeout(500);


});

test('PC-03: Tabで開いて Shift+Tabで戻りつつメニューが階層的に閉じる確認', async ({ page }) => {
  // 1. ページを開く
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // 2. メインメニューをTabでフォーカス→開く
  const mainLink = page.locator('.main-navigation .menu-item-has-children > a').first();
  await mainLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const mainItem = mainLink.locator('..');
  await expect(mainItem).toHaveClass(/active/);


  // 3. サブメニューが可視化されていることを確認
  const subMenu = mainItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 4. サブサブを持つリンクをTabでフォーカス→開く
  const subSubLink = subMenu.locator('li.menu-item-has-children > a').first();
  //await subSubLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const subSubItem = subSubLink.locator('..');
  await expect(subSubItem).toHaveClass(/active/);

  // 5. サブサブメニューが可視化されていることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(500);


  // 5. Shift+Tab 2回: サブサブメニューのフォーカスが外れる → サブサブ閉じ
  await page.keyboard.press('Shift+Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Shift+Tab');
  await page.waitForTimeout(500);

  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subSubMenu).toBeHidden();
  // サブメニューはまだ開いている
  await expect(subMenu).toBeVisible();


  //  console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@');
  //  const html_subsub = await subsubMenu.evaluate(el => el.outerHTML);
  //  console.log(html_subsub);



  // 7. Shift+Tab 三回目: メインメニューのフォーカスが外れる → メイン閉じ
  await page.keyboard.press('Shift+Tab');
  await page.waitForTimeout(500);
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();
  await page.waitForTimeout(500);

});
test('PC-04: Tabでメイン→サブ→サブサブ→次のサブ', async ({ page }) => {
  // 1. ページを開く
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // 2. メインメニューをTabでフォーカス→開く
  const mainLink = page.locator('.main-navigation .menu-item-has-children > a').first();
  await mainLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const mainItem = mainLink.locator('..');
  await expect(mainItem).toHaveClass(/active/);


  // 3. サブメニューが可視化されていることを確認
  const subMenu = mainItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 4. サブサブを持つリンクをTabでフォーカス→開く
  const subSubLink = subMenu.locator('li.menu-item-has-children > a').first();
  //await subSubLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const subSubItem = subSubLink.locator('..');
  await expect(subSubItem).toHaveClass(/active/);

  // 5. サブサブメニューが可視化されていることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(500);


  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await expect(subMenu).toBeVisible();
  await expect(subSubMenu).toBeHidden();
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);




  // 6. ESCキーを一度押して全階層を閉じる
  await page.keyboard.press('Escape');

  // 7. どの階層にも .active がなく、サブ・サブサブともに隠れていること
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();
  await expect(subSubMenu).toBeHidden();

  await page.waitForTimeout(500);


});


/****************************************************************************************:     */
/****************************************************************************************:     */
/*キーボードモバイル操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
test('モバイルでハンバーガーメニューをキーボード操作で開閉できる', async ({ page }) => {
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });

  // ページにアクセス
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // トグルボタンとメニュー取得
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');

  // 初期状態の検証（閉じている）
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

  // フォーカスしてEnterキーで開く
  await toggleButton.focus();
  await toggleButton.press('Enter');
  await page.waitForTimeout(500);

  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');

  // 再度Enterキーで閉じる
  await toggleButton.press('Enter');
  await page.waitForTimeout(500);

  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

});
test('SP-02: モバイルでTabでメイン→サブ→サブサブ開いて、ESCで全閉じ確認', async ({ page }) => {
  // 1. モバイル表示に設定してページを開く
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // 2. ハンバーガーメニューをEnterキーで開く
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');
  await toggleButton.focus();
  await toggleButton.press('Enter');
  await page.waitForTimeout(500);

  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');

  // 3. メインメニューのリンクにフォーカス→Tabで開く
  const mainLink = page.locator('.main-navigation .menu-item-has-children > a').first();
  //await mainLink.focus();
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);

  const mainItem = mainLink.locator('..');
  await expect(mainItem).toHaveClass(/active/);

  // 4. サブメニューが表示されることを確認
  const subMenu = mainItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 5. サブサブを持つリンクにTabでフォーカス→開く
  const subSubLink = subMenu.locator('li.menu-item-has-children > a').first();
  await page.keyboard.press('Tab');
  const subSubItem = subSubLink.locator('..');
  await expect(subSubItem).toHaveClass(/active/);

  // 6. サブサブメニューが表示されることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 7. ESCキーで全閉じ
  await page.keyboard.press('Escape');

  // 8. 全階層が閉じていることを確認
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();
  await expect(subSubMenu).toBeHidden();

  await page.waitForTimeout(500);
});

test('SP-03: Tabで開いて Shift+Tabで戻りつつメニューが階層的に閉じる確認', async ({ page }) => {
  // 1. モバイル表示に設定してページを開く
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  // 2. ハンバーガーメニューをEnterキーで開く
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');
  await toggleButton.focus();
  await toggleButton.press('Enter');
  await page.waitForTimeout(500);

  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');

  // 3. メインメニューのリンクにフォーカス→Tabで開く
  const mainLink = page.locator('.main-navigation .menu-item-has-children > a').first();
  //await mainLink.focus();
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Tab');
  await page.waitForTimeout(500);

  const mainItem = mainLink.locator('..');
  await expect(mainItem).toHaveClass(/active/);

  // 3. サブメニューが可視化されていることを確認
  const subMenu = mainItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 4. サブサブを持つリンクをTabでフォーカス→開く
  const subSubLink = subMenu.locator('li.menu-item-has-children > a').first();
  //await subSubLink.focus();
  await page.keyboard.press('Tab'); // メインメニュー
  const subSubItem = subSubLink.locator('..');
  await expect(subSubItem).toHaveClass(/active/);

  // 5. サブサブメニューが可視化されていることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(500);


  // 5. Shift+Tab 2回: サブサブメニューのフォーカスが外れる → サブサブ閉じ
  await page.keyboard.press('Shift+Tab');
  await page.waitForTimeout(500);
  await page.keyboard.press('Shift+Tab');
  await page.waitForTimeout(500);

  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subSubMenu).toBeHidden();
  // サブメニューはまだ開いている
  await expect(subMenu).toBeVisible();


  //  console.log('@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@');
  //  const html_subsub = await subsubMenu.evaluate(el => el.outerHTML);
  //  console.log(html_subsub);



  // 7. Shift+Tab 三回目: メインメニューのフォーカスが外れる → メイン閉じ
  await page.keyboard.press('Shift+Tab');
  await page.waitForTimeout(500);
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();
  await page.waitForTimeout(500);

});

/****************************************************************************************:     */
/****************************************************************************************:     */
/*アクセサビリティテスト*/
/****************************************************************************************:     */
/****************************************************************************************:     */
/****************************************************************************************/
/* アクセサビリティテスト：ARIA 属性の付与／解除（モバイルのみ）                       */
/****************************************************************************************/
test('アクセシビリティ: モバイルハンバーガーのaria-expanded/aria-hidden切替検証', async ({ page }) => {
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto('https://wpdev.auroralab-design.com/', { waitUntil: 'networkidle' });

  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');

  // 初期状態：閉じている
  await expect(toggleButton).toHaveAttribute('aria-expanded', 'false');
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

  // クリックで開く
  await toggleButton.click();
  await expect(toggleButton).toHaveAttribute('aria-expanded', 'true');
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');

  // もう一度クリックで閉じる
  await toggleButton.click();
  await expect(toggleButton).toHaveAttribute('aria-expanded', 'false');
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');
});
