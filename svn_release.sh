



# リリース######################################################### s
cd trunk
git fetch origin
git reset --hard origin/free
git pull 

rm -f ./inc/aurora-design-blocks-textDomain.php
rm -f .gitignore
rm -fr .github
rm -rf tests
rm -rf util
rm -rf ../assets
mv assetsForFree ../assets


ver=$(grep -i "^[[:space:]]*\*[[:space:]]*Version:" aurora-design-blocks.php | awk '{print $NF}')
cd ..

svn add --force . --auto-props --parents --depth infinity -q
cd trunk
svn copy . ../tags/$ver/
cd ..
svn commit -m "v$ver"

exit
########################################################## e

exit

svn commit -m "v1.0.1"

################## s
svn diff
svn status

vimdiff  ../aurora-design-blocks ../../aurora-design-blocks/
diff -r ../aurora-design-blocks ../../aurora-design-blocks/trunk


################## e

################## s
git remote add origin https://github.com/data2coordi/aurora-design-blocks.git
git fetch origin
git checkout -t origin/free
################## e

################## s
# 例: カレントディレクトリに *.log を無視する
svn propset svn:ignore "svn_release.sh" 
cd trunk
echo ".git" > ignore.txt
echo ".github" >> ignore.txt
echo ".gitignore" >> ignore.txt
echo "svn_release.sh" >> ignore.txt
svn propset svn:ignore -F ignore.txt .
# 確認
svn propget svn:ignore 
################## e