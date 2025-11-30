// index.js (またはフロントエンドJSファイル )

export function initializeTabsNavigation() {
  // ブロックエディター内では処理しない
  if (typeof window.wp !== "undefined" && window.wp.blocks) return;

  // コンテナを取得
  const tabContainers = document.querySelectorAll(".aurora-design-blocks-tabs");

  tabContainers.forEach((container) => {
    // タブコンテンツ（子ブロック）を取得
    const tabs = container.querySelectorAll(".tab");
    if (tabs.length === 0) return;

    // タブナビゲーションを動的に作成
    const nav = document.createElement("ul");
    nav.className = "tabs-navigation";

    tabs.forEach((tab, index) => {
      const titleElement = tab.querySelector(".tab-title h4");
      let title = titleElement ? titleElement.textContent.trim() : "";
      if (!title) {
        title = `Tab ${index + 1}`;
      }

      const li = document.createElement("li");
      li.textContent = title;

      // クリックイベントの修正: activeクラスの切り替えに変更
      li.addEventListener("click", () => {
        // 1. ナビゲーションリストの active 状態をリセット
        nav
          .querySelectorAll("li")
          .forEach((item) => item.classList.remove("active"));

        // 2. 全てのタブコンテンツから active クラスを削除
        tabs.forEach((t) => t.classList.remove("active"));

        // 3. クリックされたナビゲーションと対応するタブコンテンツに active クラスを付与
        li.classList.add("active");
        tab.classList.add("active");
      });

      nav.appendChild(li);

      // 初期表示 (最初のタブをアクティブにする)
      if (index === 0) {
        li.classList.add("active");
        tab.classList.add("active"); // ★追加: 初期状態でコンテンツ側も active にする
      }
      // ★重要: インラインスタイル (tab.style.display) の操作は削除
      // 表示制御は全て style.css の active クラスに任せるため
    });

    // コンテナの最初にナビゲーションを挿入
    container.insertBefore(nav, container.firstChild);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initializeTabsNavigation();
});
