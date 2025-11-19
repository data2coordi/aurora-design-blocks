clear

git checkout master

git push origin --delete free

# 3. ローカルの dev ブランチを強制削除
# -d は未マージを検知して停止しますが、-D は強制的に削除します
git branch -D free

# 4. 次の作業のための dev ブランチを再作成
# 最新の jmaster を基に新しい dev を作成します
git checkout -b free

git checkout master


git commit --allow-empty -m "free"
git push  --force

git checkout free
rm -rf /home/xsaurora/auroralab-design.com/public_html/wpdev.auroralab-design.com/wp-content/plugins/aurora-design-blocks/blocks
git pull 
git commit --allow-empty -m "fdouki"
git push  --force