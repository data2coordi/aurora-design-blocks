clear
TARGET_BRANCH=free
MAX_RETRY=100
#########################
# freeブランチの作り直し
#########################
git checkout master
git push origin --delete $TARGET_BRANCH
# -d は未マージを検知して停止しますが、-D は強制的に削除します
git branch -D $TARGET_BRANCH
# 4. 次の作業のための dev ブランチを再作成
# 最新の jmaster を基に新しい free を作成します
git checkout -b $TARGET_BRANCH
git push -u origin $TARGET_BRANCH







#########################
# github actions でfree環境生成
#########################
git checkout master

#"コミットでgithub actionsが実行される"
git commit --allow-empty -m "free"
git push  --force

echo "FREE環境を構築中（actions)"

#########################
# github actions でfreeをリリース
#########################

git checkout $TARGET_BRANCH
# ===== 3. アクションの commit を待つ =====
echo "GitHub Actions の commit を待っています..."

LOCAL_HASH=$(git rev-parse $TARGET_BRANCH)
RETRY=0

while [ $RETRY -lt $MAX_RETRY ]; do
    git fetch origin $TARGET_BRANCH
    REMOTE_HASH=$(git rev-parse origin/$TARGET_BRANCH)

    if [ "$LOCAL_HASH" != "$REMOTE_HASH" ]; then
        echo "新しい commit が検出されました"
        git pull origin $TARGET_BRANCH
        rm -rf /home/xsaurora/auroralab-design.com/public_html/wpdev.auroralab-design.com/wp-content/plugins/aurora-design-blocks/blocks

        echo "FREE環境をリモートからPULLしました"

        #"コミットでgithub actionsが実行される"
        git commit --allow-empty -m "fxdouki"
        git push  --force

        echo "リリースを完了しました"
        exit 0
    fi

    echo "まだ更新なし... ($((RETRY+1))/$MAX_RETRY)"
    sleep $WAIT_SEC
    RETRY=$((RETRY+1))
done

echo "タイムアウトしました"
exit 1





