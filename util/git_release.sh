
clear

#exit

#git checkout master

ver=$(grep -i "^[[:space:]]*\*[[:space:]]*Version:" ../aurora-design-blocks.php | awk '{print $NF}')
sed -i "s/define('AURORA_DESIGN_BLOCKS_VERSION', '[0-9.]*');/define('AURORA_DESIGN_BLOCKS_VERSION', '$ver');/" ../aurora-design-blocks.php
sed -i "s/^Stable tag: [0-9.]*/Stable tag: $ver/" ../readme.txt

git add ../aurora-design-blocks.php
git add ../readme.txt

git commit -m "v$ver-release  $(date '+%Y-%m-%d %H:%M:%S') douki" --allow-empty
git push 