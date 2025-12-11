#!/bin/bash
# 履歴保持型スカッシュリリースフロー

# --- !!! ユーザー設定領域 !!! ---
# リリース対象外としてコミットから除外するファイルやディレクトリをここに記述してください。
# これらは dev に復元されます。スペース区切りで複数指定可能です。
UNRELEASED_FILES="../inc/admin-page-relatedPosts.php \
                    ../blocks/related-posts/ \
                    ../inc/aurora-design-blocks-relatedPosts.php"
# -----------------------------

set -e

# --- STEP 1: devブランチでの準備 ---
echo "--- 1. devブランチでバージョン更新 ---"
git checkout dev

# ... (バージョン更新とコミットの処理) ...
ver=$(grep -i "^[[:space:]]*\*[[:space:]]*Version:" ../aurora-design-blocks.php | awk '{print $NF}')
sed -i "s/define('AURORA_DESIGN_BLOCKS_VERSION', '[0-9.]*');/define('AURORA_DESIGN_BLOCKS_VERSION', '$ver');/" ../aurora-design-blocks.php
sed -i "s/^Stable tag: [0-9.]*/Stable tag: $ver/" ../readme.txt
git add ../aurora-design-blocks.php ../readme.txt
git commit --allow-empty -m "v$ver-release prep at dev"

# --- STEP 2: masterへの安全な統合とコミット ---
echo "--- 2. masterへのスカッシュマージ準備 ---"
git checkout master
git pull origin master

git merge --squash dev

echo "--- 2-2. リリース対象外ファイルの除外（ステージング解除） ---"
for file in $UNRELEASED_FILES; do
    git reset HEAD -- "$file" # masterの履歴を健全に保つ
done

git commit -m "douki v$ver-release"

# --- STEP 3: 検証環境のクリーンアップとデプロイ ---
echo "--- 3. 検証環境のクリーンアップ ---"
# 1. カレントディレクトリを保存
CURRENT_DIR=$(pwd)

# 2. プロジェクトルートへ移動 (カレントは ./util なので ../ がルート)
cd ..

# 3. クリーンアップ実行 (-fx で .gitignore 対象も含めて削除)
# 前回、master で untracked files として残っていたため -fd ではなく -fx を使用
git clean -fx

# 4. 元のディレクトリに戻る
cd "$CURRENT_DIR"


echo "--- masterをリモートへプッシュ ---"
git push origin master

# --- STEP 4: devブランチの更新とファイルの復元 ---
echo "--- 4. devの更新とファイル復元 ---"

git checkout dev

# 4-2. masterをdevにマージ (masterの削除差分がdevに反映される)
git merge master

# 4-3. masterへのマージによって削除されたファイルを復元する (履歴保持の鍵)
echo "--- 4-3. リリース対象外だった機能をdevに復元 ---"
# HEAD^ は merge master の直前のコミット（devの最後の開発コミット）を指す
for file in $UNRELEASED_FILES; do
    # devの履歴を基に、ファイルを作業ツリーに復元 
    git checkout HEAD^ -- "$file"
done

# 4-4. 復元されたファイルを再度コミット
git add .
git commit -m "リリース対象外ファイルを復元＆復元されたファイルを再度コミット"

# 4-5. リモートのdevにプッシュ
git push origin dev

echo "--- リリース完了 (v$ver) とdevの準備完了 ---"
exit 0