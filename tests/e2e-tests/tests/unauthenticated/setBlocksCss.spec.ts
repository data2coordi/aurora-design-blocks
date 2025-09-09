import { test, expect } from "@playwright/test";

// 投稿ページ URL（ブロックが存在する）
const BASE_URL = "/ptest/";
// 非記事ページ URL（ブロックが存在しない）
const BASE_URL_NO_BLOCKS = "/sidefire-7500man-life-cost/";

// チェック対象ブロック CSS のパス（末尾のみで判定）
const BLOCK_CSS = [
  "blocks/cta-block/build/style-index.css",
  "blocks/custom-cover/build/style-index.css",
  "blocks/frame-line/build/style-index.css",
  "blocks/slider-block/build/style-index.css",
  "blocks/speech-bubble/build/style-index.css",
  "blocks/tab-block/build/style-index.css",
  "blocks/text-flow-animation/build/style-index.css",
];

// 投稿ページ：全ブロックの CSS がフロントに反映されるか + 遅延ロードチェック
test("全ブロックの CSS がフロントに反映される（デバッグ付き）", async ({
  page,
}) => {
  await page.goto(BASE_URL);

  // ページ内の全 CSS link を取得
  const linkHandles = await page.$$('link[rel="stylesheet"]');
  const hrefs = await Promise.all(
    linkHandles.map(async (link) => await link.getAttribute("href"))
  );
  console.log("ページに存在する CSS link:", hrefs);

  for (const cssPath of BLOCK_CSS) {
    // linkHandle を取得（遅延ロードの有無確認用）
    const linkHandle = await page
      .waitForSelector(`link[href*="${cssPath}"]`, { timeout: 5000 })
      .catch(() => null);

    // 絶対 URL と ?ver パラメータに対応してチェック
    const found = hrefs.some(
      (href) => href?.endsWith(cssPath) || href?.includes(`/${cssPath}?ver=`)
    );
    console.log(`チェック中: ${cssPath} -> found: ${found}`);

    if (!found) console.error(`CSS が見つかりません: ${cssPath}`);

    expect(found).toBeTruthy(); // CSS が存在することを確認

    // 遅延ロードであるかもチェック（rel=preload）
    if (linkHandle) {
      const relAttr = await linkHandle.getAttribute("rel");
      if (relAttr === "preload") {
        console.log(`CSS が遅延ロードされる: ${cssPath}`);
      }
    }
  }
});

// 非記事ページ：ブロック CSS が読み込まれないか
test("非記事ページでは全ブロックの CSS が読み込まれない（デバッグ付き）", async ({
  page,
}) => {
  await page.goto(BASE_URL_NO_BLOCKS);

  const linkHandles = await page.$$('link[rel="stylesheet"]');
  const hrefs = await Promise.all(
    linkHandles.map(async (link) => await link.getAttribute("href"))
  );
  console.log("非記事ページに存在する CSS link:", hrefs);

  for (const cssPath of BLOCK_CSS) {
    const found = hrefs.some(
      (href) => href?.endsWith(cssPath) || href?.includes(`/${cssPath}?ver=`)
    );
    console.log(`非記事ページチェック: ${cssPath} -> found: ${found}`);

    if (found)
      console.error(`非記事ページで CSS が読み込まれています: ${cssPath}`);

    expect(found).toBeFalsy(); // CSS が読み込まれていないことを確認
  }
});
