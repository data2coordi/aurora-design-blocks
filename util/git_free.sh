git checkout master
git commit --allow-empty -m "free"
git push  --force

git checkout free
rm -rf /home/xsaurora/auroralab-design.com/public_html/wpdev.auroralab-design.com/wp-content/plugins/aurora-design-blocks/blocks
git pull 
git commit --allow-empty -m "fdouki"
git push  --force