#!/bin/bash
# 履歴保持型スカッシュリリースフロー (クリーンアップ一本化版)

# --- !!! ユーザー設定領域 !!! ---
UNRELEASED_FILES="../inc/admin-page-relatedPosts.php \
                  ../blocks/related-posts/ \
                  ../inc/aurora-design-blocks-relatedPosts.php"
# -----------------------------

set -e

# --- STEP 1: devブランチでの準備 (変更なし) ---
echo "--- 1. devブランチでバージョン更新 ---"
git checkout dev
# ... (バージョン更新とコミットの処理) ...

# --- STEP 2: masterへの安全な統合とコミット (変更なし) ---
echo "--- 2. masterへのスカッシュマージ準備 ---"
git checkout master
git pull origin master
git merge --squash dev

echo "--- 2-2. リリース対象外ファイルの除外（ステージング解除） ---"
for file in $UNRELEASED_FILES; do
    git reset HEAD -- "$file" # masterの履歴を健全に保つ
done
git commit --allow-empty -m "douki v$ver-release"

# --- STEP 3: 検証環境のクリーンアップとデプロイ (簡素化) ---
echo "--- 3. 検証環境のクリーンアップ (プッシュ前クリーンアップ省略) ---"
# ここでは、git clean を実行せず、未コミットの変更がない状態を維持するのみ。

echo "--- masterをリモートへプッシュ ---"
git push origin master

# --- STEP 4: devブランチの更新とファイルの復元 ---
echo "--- 4. devの更新とファイル復元 ---"

# 4-1. ブランチ切り替え前に残っている追跡対象外ファイルを強制削除 (★ここがクリーンアップの実行点)
echo "--- 4-1. 最後のクリーンアップ：ブランチ切り替え前の作業ツリーを完全にクリーンに ---"
CURRENT_DIR=$(pwd)
cd ..
# 追跡されていないファイル（.gitignore対象含む）を強制削除
git clean -fd
cd "$CURRENT_DIR"

# 4-2. devブランチに戻る
git checkout dev

# 4-3. masterをdevにマージ (masterの削除差分がdevに反映される)
git merge master --no-edit --no-ff

# 4-4. masterへのマージによって削除されたファイルを復元する (履歴保持の鍵)
echo "--- 4-4. リリース対象外だった機能をdevに復元 ---"
for file in $UNRELEASED_FILES; do
    git checkout HEAD^ -- "$file"
done

# 4-5. 復元されたファイルを再度コミット
git add .
git commit -m "リリース対象外ファイルを復元＆復元されたファイルを再度コミット"

# 4-6. リモートのdevにプッシュ
git push origin dev

echo "--- リリース完了 (v$ver) とdevの準備完了 ---"
#/usr/bin/php8.3 $(which composer) update 
exit 0
