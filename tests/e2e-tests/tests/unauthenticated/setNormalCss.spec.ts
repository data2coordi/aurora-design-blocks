// @ts-check
const { test, expect } = require("@playwright/test");

// テスト対象のWordPressサイトのURLをここに設定してください。
const WORDPRESS_SITE_URL = "";

test.describe("AuroraDesignBlocks CSSアセットのテスト", () => {
  // 単一記事ページでのCSS読み込みをテストします
  test("単一記事ページで正しいCSSが読み込まれること", async ({ page }) => {
    // WordPressサイトの単一記事ページに移動します
    await page.goto(`${WORDPRESS_SITE_URL}/sidefire-7500man-life-cost/`);

    // 'module.css' が読み込まれているか、そのIDを使って確認します
    const moduleCssLink = page.locator(
      "#aurora-design-blocks-style-module-css"
    );
    await expect(moduleCssLink).toHaveAttribute(
      "href",
      // 実際に取得されたURLに「css/build/module.css」が含まれているか確認
      /css\/build\/module\.css/
    );

    // 'aurora-design.css' が読み込まれているか、そのIDを使って確認します
    const designCssLink = page.locator(
      "#aurora-design-style-aurora-design-css"
    );
    await expect(designCssLink).toHaveAttribute(
      "href",
      // 実際に取得されたURLに「css/build/aurora-design.css」が含まれているか確認
      /css\/build\/aurora-design\.css/
    );

    // 'awesome-all.css' が読み込まれていないことを確認します
    const awesomeLink = page.locator(
      'head link[rel="stylesheet"][href$="awesome-all.css"]'
    );
    await expect(awesomeLink).not.toHaveCount(1);
  });

  // Font Awesomeショートコードを含む単一記事ページでのCSS読み込みをテストします
  test("fontawesomeを含む単一記事ページでCSSが読み込まれること", async ({
    page,
  }) => {
    // Font Awesomeショートコードを含む単一記事ページに移動します
    //urlはfireで自由と成長を掴む！/
    await page.goto(
      `${WORDPRESS_SITE_URL}/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81//`
    );

    // 'awesome-all.css' が読み込まれていることを確認します
    // IDを使って要素がDOMにアタッチされるまで待機します
    const awesomeLink = page.locator("#aurora-design-style-awesome-css");
    await expect(awesomeLink).toHaveCount(1);
    await expect(awesomeLink).toHaveAttribute(
      "href",
      /css\/build\/awesome-all\.css/
    );
  });

  // アーカイブページでのCSS読み込みをテストします
  test("アーカイブページで正しいCSSが読み込まれること", async ({ page }) => {
    // WordPressサイトのカテゴリーアーカイブページに移動します
    await page.goto(`${WORDPRESS_SITE_URL}/fire-blog/`);

    // 'module.css' が読み込まれているか、IDを使って確認します
    const moduleCssLink = page.locator(
      "#aurora-design-blocks-style-module-css"
    );
    await expect(moduleCssLink).toHaveAttribute(
      "href",
      /css\/build\/module\.css/
    );

    // 'aurora-design.css' が読み込まれているか、IDを使って確認します
    const designCssLink = page.locator(
      "#aurora-design-style-aurora-design-css"
    );
    await expect(designCssLink).toHaveAttribute(
      "href",
      /css\/build\/aurora-design\.css/
    );
  });

  // 404ページでのCSS読み込みをテストします
  test("404ページで正しいCSSが読み込まれること", async ({ page }) => {
    // 存在しないURLに移動して404ページを生成します
    await page.goto(`${WORDPRESS_SITE_URL}/non-existent-page-12345/`);

    // 'module.css' が読み込まれているか、IDを使って確認します
    const moduleCssLink = page.locator(
      "#aurora-design-blocks-style-module-css"
    );
    await expect(moduleCssLink).toHaveAttribute(
      "href",
      /css\/build\/module\.css/
    );

    // 'aurora-design.css' が読み込まれているか、IDを使って確認します
    const designCssLink = page.locator(
      "#aurora-design-style-aurora-design-css"
    );
    await expect(designCssLink).toHaveAttribute(
      "href",
      /css\/build\/aurora-design\.css/
    );
  });
});
