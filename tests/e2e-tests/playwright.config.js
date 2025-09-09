import { defineConfig, devices } from "@playwright/test";

// 環境によってURLや認証ファイルパスが変わるため、定数として定義
const BASE_URL = "https://wpdev.auroralab-design.com";
const authFile = "playwright/.auth/user.json";

export default defineConfig({
  // 各テストのデフォルトタイムアウト（ms）
  timeout: 60_000,

  // プロジェクト間で共有される設定
  use: {
    // click や fill など1アクションのタイムアウト
    actionTimeout: 30_000,
    // 動画録画設定
    video: "off",
    // ブラウザのベースURL
    baseURL: BASE_URL,
  },

  // 複数のテストプロジェクトを定義
  projects: [
    {
      name: "setup",
      testDir: "./tests", // テストファイルのディレクトリを指定
      testMatch: "auth.setup.ts",
    },

    // 2. 【新規】認証が不要なテスト用のプロジェクト
    {
      name: "unauthenticated",
      testDir: "./tests/unauthenticated", // テストファイルのディレクトリを指定
      use: {
        ...devices["Desktop Chrome"],
      },
    },
    {
      name: "main",
      testDir: "./tests/main", // テストファイルのディレクトリを指定
      testMatch: [/customiser\.spec\.ts/], // 認証不要なテストファイルを指定
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
      },
    },
  ],
});
